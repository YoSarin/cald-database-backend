FROM php:5-apache

COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/php.ini

RUN docker-php-ext-install pdo pdo_mysql gettext

RUN a2enmod rewrite

RUN apt-get update -y
RUN apt-get install -y python3 python3-pip netcat
ENV PYTHONENCODING=utf8

COPY ./init.sh /bin/init.sh
RUN chmod +x /bin/init.sh

EXPOSE 80