FROM php:5.5-apache
MAINTAINER Rion Dooley <dooley@tacc.utexas.edu>

# # Set environmental variables
# ENV COMPOSER_HOME /root/composer
#
# # Install Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
#     a2enmod rewrite
#
# Add project from current repo to enable automated build
ADD . /var/www/html

# Change ownership for apache happiness
RUN a2enmod rewrite && chown -R www-data:www-data /var/www/html

# Override restrictive defaults of base image
ADD docker/apache2.conf /etc/apache2/apache2.conf
