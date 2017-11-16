#!/bin/bash
set -e


# Change ownership for apache happiness
chown -R www-data:www-data "${APACHE_DOCROOT}"

if [[ -n "$BEANSTALKD_HOST" ]]; then

  if [[ -z "$BEANSTALKD_PORT" ]]; then
    BEANSTALKD_PORT=11300
  fi

  sed -ir "s/'servers'.*$/'servers'=> array('Default Beanstalkd' => 'beanstalk:\/\/$BEANSTALKD_HOST:$BEANSTALKD_PORT'),/g" /var/www/config.php

elif [[ -n "$BEANSTALKD_PORT_11300_TCP_ADDR" ]]; then

  BEANSTALKD_HOST=$BEANSTALKD_PORT_11300_TCP_ADDR

  if [[ -z "$BEANSTALKD_PORT" ]]; then
    if [[ -n "$BEANSTALKD_PORT_11300_TCP_PORT" ]]; then
      BEANSTALKD_PORT=$BEANSTALKD_PORT_11300_TCP_PORT
    fi
  else
    BEANSTALKD_PORT=11300
  fi

  sed -ir "s/'servers'.*$/'servers'=> array('Default Beanstalkd' => 'beanstalk:\/\/$BEANSTALKD_HOST:$BEANSTALKD_PORT'),/g" /var/www/config.php

fi

rm -f /var/run/apache2/apache2.pid

source /etc/apache2/envvars && exec /usr/sbin/apache2 -DFOREGROUND
