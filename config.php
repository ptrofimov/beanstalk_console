<?php

$config = array(
    /**
     * List of servers available for all users
     */
    'servers' => array(/* 'beanstalk://localhost:11300', ... */),
    /**
     * Saved samples jobs are kept in this file, must be writable
     */
    'storage' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage.json',
    /**
     * Version number
     */
    'version' => '1.6.1',
);
