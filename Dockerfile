FROM php:8.2-fpm

# Instala dependências básicas, GPG 
RUN apt-get update && apt-get install -y \
    curl \
    git \
    zip \
    unzip \
    gnupg \
    ca-certificates

# Instala extensões PHP comuns
RUN docker-php-ext-install pdo pdo_mysql

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia o código do projeto (opcional)
COPY ./app /var/www/html

# Permissões
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
