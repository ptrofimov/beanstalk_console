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
     * Version number
     */
    'version' => '1.7.6',
);

/**
 * You can also put your overrides in local_config.php
 */

$local_config = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local_config.php';
if(file_exists($local_config) && is_file($local_config)) {
    include $local_config;
}
