FROM php:5.6-apache
LABEL maintainer="Rion Dooley <dooley@tacc.utexas.edu>"

# Add php extensions
RUN docker-php-ext-install mbstring && \
    a2enmod rewrite

# Add project from current repo to enable automated build
WORKDIR /var/www
ADD . ./

# Add custom default apache virutal host with combined error and access
# logging to stdout
ADD docker/apache_default /etc/apache2/apache2.conf
ADD docker/apache_vhost  /etc/apache2/sites-available/000-default.conf
ADD docker/php.ini /usr/local/etc/php

# Add custom entrypoint to inject runtime environment variables into
# beanstalk console config
ADD docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

# Change ownership for apache happiness
RUN chmod +x /usr/local/bin/docker-entrypoint && \
    chown -R www-data:www-data /var/www

CMD ["/usr/local/bin/docker-entrypoint"]
