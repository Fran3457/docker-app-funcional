# # Backend PHP + Apache
# FROM php:8.2-apache

# # Instalar extensiones PHP para MySQL
# RUN docker-php-ext-install pdo pdo_mysql mysqli

# COPY ../*.php /var/www/html
















# apache/Dockerfile

# --- ETAPA 1: Construir el Frontend (Node.js) ---
FROM node:22 as build-stage
WORKDIR /app
# Copiamos el package.json y package-lock.json primero (para aprovechar caché)
COPY FrontendReto-2/package*.json ./
RUN npm install
# Copiamos todo el código fuente de Vue
COPY FrontendReto-2/ .
# Compilamos (generamos la carpeta dist)
RUN npm run build

# --- ETAPA 2: Servidor Web (PHP + Apache) ---
FROM php:8.2-apache
WORKDIR /var/www/html

# 1. Instalamos la extensión mysqli que necesita tu PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 2. Activamos mod_rewrite para que funcionen tus .htaccess
RUN a2enmod rewrite

# 3. Copiamos el Backend
# Creamos la carpeta backend y metemos tus archivos PHP
RUN mkdir backend
COPY backend/ /var/www/html/backend/

# 4. Copiamos el Frontend compilado desde la ETAPA 1
# Copiamos el contenido de 'dist' a la raíz del servidor
COPY --from=build-stage /app/dist /var/www/html/

# 5. Configuración extra de Apache para permitir .htaccess
# Esto asegura que Apache lea tus reglas de reescritura
RUN echo '<Directory /var/www/html/>' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

    # CREAR CARPETA DE IMÁGENES Y DAR PERMISOS
    
RUN chown -R www-data:www-data /var/www/html/backend/img
RUN chmod -R 777 /var/www/html/backend/img

EXPOSE 80