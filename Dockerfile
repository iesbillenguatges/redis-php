FROM php:8.2-apache

RUN apt-get update && apt-get install -y \ 
    libssl-dev     && pecl install redis     && docker-php-ext-enable redis

COPY index.php /var/www/html/

EXPOSE 80

CMD ["apache2-foreground"]
