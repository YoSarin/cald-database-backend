FROM php:5-apache

COPY docker/release/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/release/php.ini /usr/local/etc/php/php.ini

RUN mkdir /var/www/cald-database-backend
COPY . /var/www/cald-database-backend
COPY ./data/update.py.requirements.txt /var/www/cald-database-backend 

RUN touch /var/www/cald-database-backend/public/.env
RUN chown www-data:www-data /var/www/cald-database-backend/logs
RUN docker-php-ext-install pdo pdo_mysql gettext

RUN a2enmod rewrite

RUN apt-get update -y
RUN apt-get install -y python3 python3-pip
ENV PYTHONENCODING=utf8
RUN pip3 install -r /var/www/cald-database-backend/update.py.requirements.txt

EXPOSE 80
