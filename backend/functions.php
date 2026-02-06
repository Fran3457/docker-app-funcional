<?php
// functions.php

// ==========================================
// FUNCIONES DE AUTENTICACIÓN
// ==========================================

function login($mysqli, $input) {
    if (!isset($input['email']) || !isset($input['password'])) { 
        http_response_code(400); return; 
    }
    
    $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $input['email']);
    $stmt->execute();
    
    // REQUISITO PROFESOR: Obtener objeto recurso
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($input['password'], $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            echo json_encode(["message" => "Login exitoso", "role" => $row['role'], "username" => $row['username']]);
        } else {
            http_response_code(401); echo json_encode(["error" => "Contraseña incorrecta"]);
        }
    } else {
        http_response_code(404); echo json_encode(["error" => "Usuario no encontrado"]);
    }
}

function register($mysqli, $input) {
    // 1. VALIDACIÓN BÁSICA (Mejorada: usamos empty para evitar cadenas vacías "")
    if (empty($input['username']) || empty($input['email']) || empty($input['password'])) { 
        http_response_code(400); 
        echo json_encode(["error" => "Rellena todos los campos"]);
        return; 
    }

    // 2. COMPROBAR SI YA EXISTE (El paso que faltaba)
    $check = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $input['email']);
    $check->execute();
    $check->store_result(); // Necesario para contar las filas

    if ($check->num_rows > 0) {
        // Ya existe un usuario con ese email
        http_response_code(409); // 409 = Conflicto
        echo json_encode(["error" => "Este correo electrónico ya está registrado"]);
        return;
    }
    $check->close();

    // 3. SI NO EXISTE, PROCEDEMOS AL REGISTRO
    $hash = password_hash($input['password'], PASSWORD_BCRYPT);
    
    // Asignamos rol USER por defecto
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'USER')");
    $stmt->bind_param("sss", $input['username'], $input['email'], $hash);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Registrado correctamente"]);
    } else { 
        http_response_code(500); 
        echo json_encode(["error" => "Error al registrar en la base de datos"]); 
    }
}

function logout() {
    session_destroy();
    echo json_encode(["message" => "Sesión cerrada"]);
}

// ==========================================
// FUNCIONES DE DATOS (API PÚBLICA / PRIVADA)
// ==========================================

function obtenerJuegosFiltrados($mysqli, $busqueda) {
    // 1. SOLUCIÓN ESPACIOS: Limpiamos la búsqueda antes de nada
    // trim() quita espacios del principio y final.
    // Si alguien busca "  PC  ", esto lo convierte en "PC"
    $busqueda = trim($busqueda);

    $sql = "SELECT * FROM games WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($busqueda)) {
        // 2. SOLUCIÓN ACENTOS Y MAYÚSCULAS:
        // Usamos 'COLLATE utf8mb4_unicode_ci' después de cada columna.
        // Esto le dice a MySQL: "Compara esto ignorando mayúsculas y acentos".
        // Así 'movil' es igual a 'Móvil' y 'accion' es igual a 'Acción'.
        
        $sql .= " AND (
            titulo COLLATE utf8mb4_unicode_ci LIKE ? OR 
            genero COLLATE utf8mb4_unicode_ci LIKE ? OR 
            plataformas COLLATE utf8mb4_unicode_ci LIKE ?
        )";
        
        $parametro = "%" . $busqueda . "%";
        
        $params[] = $parametro;
        $params[] = $parametro;
        $params[] = $parametro;
        $types .= "sss";
    }

    $stmt = $mysqli->prepare($sql);
    
    // Si salta error de "Collation mix", avísame, pero con utf8mb4 debería ir perfecto.
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    // Decodificar el JSON de plataformas
    foreach ($data as &$juego) {
        if (isset($juego['plataformas'])) {
            $decoded = json_decode($juego['plataformas']);
            if (json_last_error() === JSON_ERROR_NONE) {
                $juego['plataformas'] = $decoded;
            }
        }
    }

    echo json_encode($data);
}

function obtenerEventos($mysqli, $page, $tipo, $fecha, $soloLibres, $userId = null) {
    if ($page < 1) $page = 1;
    $limit = 9; 
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM events WHERE 1=1";
    $params = [];
    $types = "";

    if ($tipo !== 'todos' && !empty($tipo)) { $sql .= " AND tipo = ?"; $params[] = $tipo; $types .= "s"; }
    if (!empty($fecha)) { $sql .= " AND fecha = ?"; $params[] = $fecha; $types .= "s"; }
    if ($soloLibres === '1' || $soloLibres === 'true') { $sql .= " AND plazasLibres > 0"; }

    $sql .= " ORDER BY fecha ASC, hora ASC LIMIT ? OFFSET ?";
    $params[] = $limit; $params[] = $offset; $types .= "ii";

    $stmt = $mysqli->prepare($sql);
    if (!empty($types)) { $stmt->bind_param($types, ...$params); }

    $stmt->execute();
    
    // REQUISITO PROFESOR: get_result() y fetch_all()
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($data);
}

function inscribirseEvento($mysqli, $eventId, $userId) {
    $check = $mysqli->prepare("SELECT * FROM user_events WHERE user_id = ? AND event_id = ?");
    $check->bind_param("ii", $userId, $eventId);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) { 
        http_response_code(409); echo json_encode(["error" => "Ya estas inscrito al evento"]); return; 
    }

    $mysqli->begin_transaction();
    try {
        $stmt2 = $mysqli->prepare("UPDATE events SET plazasLibres = plazasLibres - 1 WHERE id = ? AND plazasLibres > 0");
        $stmt2->bind_param("i", $eventId);
        $stmt2->execute();
        if ($stmt2->affected_rows === 0) throw new Exception("Sin plazas");

        $stmt1 = $mysqli->prepare("INSERT INTO user_events (user_id, event_id) VALUES (?, ?)");
        $stmt1->bind_param("ii", $userId, $eventId);
        $stmt1->execute();

        $mysqli->commit();
        echo json_encode(["message" => "Inscrito con éxito"]);
    } catch (Exception $e) {
        $mysqli->rollback();
        http_response_code(400); echo json_encode(["error" => $e->getMessage()]);
    }
}

function desapuntarseEvento($mysqli, $eventId, $userId) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli->begin_transaction();
    try {
        $stmt1 = $mysqli->prepare("DELETE FROM user_events WHERE user_id = ? AND event_id = ?");
        $stmt1->bind_param("ii", $userId, $eventId);
        $stmt1->execute();

        if ($stmt1->affected_rows > 0) {
            $stmt2 = $mysqli->prepare("UPDATE events SET plazasLibres = plazasLibres + 1 WHERE id = ?");
            $stmt2->bind_param("i", $eventId);
            $stmt2->execute();
        }

        $mysqli->commit();
        echo json_encode(["message" => "Desapuntado"]);
    } catch (Exception $e) {
        $mysqli->rollback();
        http_response_code(500); 
        echo json_encode(["error" => "Fallo SQL: " . $e->getMessage()]);
    }
}

function obtenerMisEventos($mysqli, $userId) {
    $sql = "SELECT e.* FROM events e JOIN user_events ue ON e.id = ue.event_id WHERE ue.user_id = ? ORDER BY e.fecha ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // REQUISITO PROFESOR: get_result() y fetch_all()
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function crearEvento($mysqli, $input) {
    // 1. Manejo de la IMAGEN
    $nombreImagen = 'default.png'; // Valor por defecto

    // DEBUG: Verificar si PHP está recibiendo el archivo
    if (isset($_FILES['imagen'])) {
        $file = $_FILES['imagen'];
        
        // CASO A: Error de subida (Tamaño, interrupción, etc.)
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400); 
            // Códigos de error: 1 = Excede upload_max_filesize, 2 = Excede MAX_FILE_SIZE
            echo json_encode(["error" => "Error de subida PHP Código: " . $file['error']]); 
            return; // Cortamos aquí para que lo veas
        }

        $dir = __DIR__ . '/img'; 
        
        // CASO B: La carpeta no existe o no tiene permisos
        if (!is_writable($dir)) {
            http_response_code(500);
            echo json_encode(["error" => "La carpeta 'img' no tiene permisos de escritura (777)"]);
            return;
        }

        // Validar MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($mime, $permitidos)) {
            http_response_code(400);
            echo json_encode(["error" => "Archivo no permitido: $mime"]);
            return;
        }

        // Subir
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nuevoNombre = bin2hex(random_bytes(8)) . "." . $ext;
        $destino = $dir . '/' . $nuevoNombre;

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            $nombreImagen = $nuevoNombre;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Falló move_uploaded_file. ¿Ruta correcta?"]);
            return;
        }
    }

    // 2. Insertar en Base de Datos
    $titulo = $_POST['titulo'] ?? $input['titulo'];
    $tipo = $_POST['tipo'] ?? $input['tipo'];
    $fecha = $_POST['fecha'] ?? $input['fecha'];
    $hora = $_POST['hora'] ?? $input['hora'];
    $plazas = $_POST['plazasLibres'] ?? $input['plazasLibres'];
    $desc = $_POST['descripcion'] ?? $input['descripcion'];
    $creador = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("INSERT INTO events (titulo, tipo, fecha, hora, plazasLibres, imagen, descripcion, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissi", $titulo, $tipo, $fecha, $hora, $plazas, $nombreImagen, $desc, $creador);
    
    if ($stmt->execute()) echo json_encode(["message" => "Creado", "imagen" => $nombreImagen]);
    else { http_response_code(500); echo json_encode(["error" => "Error SQL"]); }
}
?>