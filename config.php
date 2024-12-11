<?php

$GLOBALS['config'] = array(
    /**
     * List of servers available for all users
     */
    'servers' => array(/* 'Local Beanstalkd' => 'beanstalk://localhost:11300', ... */),
    /**
     * Saved samples jobs are kept in this file, must be writable
     */
    'storage' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage.json',
    /**
     * Optional Basic Authentication
     */
    'auth' => array(
        'enabled' => false,
        'username' => 'admin',
        'password' => 'password',
    ),
    /**
     * Optional retry configuration for retrying data fetching from beanstalk
     */
    'retry' => [
        /**
         * Retry failed beanstalk data fetching if hitting a server exception
         */
        'shouldRetry' => false,
        /**
         * Max amount of tries it will make when trying to fetch beanstalk data, each with a default delay of 250ms.
         * It is ignored if shouldRetry is false
         */
        'maxTries' => 2,
        /**
         * Custom delay in milliseconds between each fetch retry (default 250ms)
         * It is ignored if shouldRetry is false
        */
        'delay' => 250
    ],
    /**
     * Version number
     */
    'version' => '1.7.21',
);
