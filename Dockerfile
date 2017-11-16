FROM php:5.6-apache
LABEL maintainer="Rion Dooley <dooley@tacc.utexas.edu>"

ENV APACHE_DOCROOT "/var/www"

# Add php extensions
RUN docker-php-ext-install mbstring && \
    a2enmod rewrite

# Add custom default apache virutal host with combined error and access
# logging to stdout
ADD docker/apache_vhost  /etc/apache2/sites-available/000-default.conf
ADD docker/php.ini /usr/local/etc/php

# Add custom entrypoint to inject runtime environment variables into
# beanstalk console config
ADD docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint
CMD ["/usr/local/bin/docker-entrypoint"]

# Add project from current repo to enable automated build
WORKDIR "${APACHE_DOCROOT}"
ADD . ./
