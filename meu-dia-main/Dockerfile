FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY projetointegrador/ /var/www/html/

# Keep development exports and database scripts out of the public document root.
RUN rm -f /var/www/html/database.sql \
    /var/www/html/files.zip \
    "/var/www/html/script do banco valoriza.txt" \
    /var/www/html/paginaprincipal.html.txt

# Serve the public landing page at /; the dashboard itself stays at /index.php.
RUN sed -ri 's!DirectoryIndex.*!DirectoryIndex inicial.html index.php index.html!' /etc/apache2/mods-available/dir.conf

EXPOSE 80
