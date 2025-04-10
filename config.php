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
     * Default UI settings (used when no cookie is present).
     * These values will be overridden by user-specific selection in Settings screen and kept in cookies.
     * Keys use a positive 'enableFeature' convention where applicable.
     * 'true' means the feature is ON by default.
     * 'false' means the feature is OFF by default.
     */
    'settings' => array(
        // Numeric settings
        'tubePauseSeconds'          => -1,    // Default: -1 (uses beanstalkd default of 1 hour)
        'autoRefreshTimeoutMs'      => 500,   // Default: 500ms interval for auto-refresh
        'searchResultLimit'         => 25,    // Default: 25 results in search

        // Boolean settings (true = enabled/checked by default)
        'enableJsonDecode'          => true,  // Default: Job data IS json_decoded by default
        'enableJobDataHighlight'    => true,  // Default: Job data highlighting IS enabled by default
        'enableAutoRefreshLoad'     => false,  // Default: Auto-refresh IS disabled on page load
        'enableUnserialization'     => false, // Default: Job data IS NOT unserialized by default
        'enableBase64Decode'        => false, // Default: Job data IS NOT base64_decoded by default
    ),

    /**
     * Version number
     */
    'version' => '1.8.0', // Consider updating if you modify core functionality
);
