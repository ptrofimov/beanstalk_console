#!/bin/bash
set -e

PHP_ERROR_REPORTING=${PHP_ERROR_REPORTING:-"E_ALL & ~E_DEPRECATED & ~E_NOTICE"}
sed -ri 's/^display_errors\s*=\s*Off/display_errors = On/g' /usr/local/lib/php.ini
sed -ri "s/^error_reporting\s*=.*$//g" /usr/local/lib/php.ini
echo "error_reporting = $PHP_ERROR_REPORTING" >> /usr/local/lib/php.ini

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

source /etc/apache2/envvars && exec /usr/sbin/apache2 -DFOREGROUND
