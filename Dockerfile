FROM php:5.5-apache
MAINTAINER Rion Dooley <dooley@tacc.utexas.edu>

# Add project from current repo to enable automated build
ADD . /var/www/html

# Change ownership for apache happiness
RUN a2enmod rewrite && chown -R www-data:www-data /var/www/html

# Override restrictive defaults of base image
ADD docker/apache2.conf /etc/apache2/apache2.conf
