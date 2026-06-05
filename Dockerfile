FROM php:8.4-apache
LABEL maintainer="Rion Dooley <dooley@tacc.utexas.edu>"

ENV APACHE_DOCROOT="/var/www"

# Configure Apache to listen on port 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf

# Add custom default apache virtual host with combined error and access logging to stdout
COPY docker/apache_vhost /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php

WORKDIR "${APACHE_DOCROOT}"
COPY . ./

# Ensure Apache runtime directories and docroot are owned by www-data
RUN chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2 "${APACHE_DOCROOT}"

USER www-data

CMD ["apache2-foreground"]
