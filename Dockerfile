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
ADD docker/apache_vhost_ssl /etc/apache2/sites-available/default-ssl.conf
ADD docker/php.ini /usr/local/lib/php.ini

# Add custom entrypoint to inject runtime environment variables into
# beanstalk console config
ADD docker/run.sh /usr/local/bin/run

# Change ownership for apache happiness
RUN chmod +x /usr/local/bin/run && \
    chown -R www-data:www-data /var/www


CMD ["/usr/local/bin/run"]
