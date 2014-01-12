#!/usr/bin/env bash

# Update remote package metadata
apt-get update -q

# Install deb dependencies
apt-get install -y \
	curl \
	php5 \
	php5-json \
	php5-curl \
	apache2 \

# Setup apache virtualhost
cat /vagrant/vagrant/virtualhost >> /etc/apache2/httpd.conf
service apache2 restart
