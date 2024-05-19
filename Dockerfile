# Define a imagem base
FROM php:8.2-apache

# Instala o Git
RUN apt-get update && apt-get install -y git

# Habilita os módulos do Apache necessários para o funcionamento do PHP
RUN a2enmod rewrite

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define o diretório de trabalho como o diretório padrão do Apache
WORKDIR /var/www/html