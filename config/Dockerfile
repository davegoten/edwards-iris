FROM php:8.2.5-apache-buster as webserver

RUN apt-get update && apt-get upgrade -y && \
    apt-get install bash libzip-dev -y && \
    apt-get clean && \
    docker-php-ext-install mysqli pdo_mysql zip && \
    a2enmod rewrite && \
    mkdir -p /var/lib/mysql

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin --filename=composer --version=2.4.3 && pwd

ADD ./config/php.ini /usr/local/etc/php/php.ini
ADD ./ /var/www
RUN rm -rf /var/www/config && chown -R www-data:www-data /var/www/html

ADD ./config/entry.sh /var/entry.sh
RUN chmod +x /var/entry.sh

EXPOSE 80
WORKDIR /var/www/

ENTRYPOINT ["/var/entry.sh"] 
