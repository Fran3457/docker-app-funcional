FROM mysql:8.4

# Copiar SQL al contenedor para inicializar la base de datos
COPY gamefest.sql /docker-entrypoint-initdb.d/

CMD ["mysqld", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]