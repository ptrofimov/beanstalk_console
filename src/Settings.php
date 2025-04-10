<?php

/**
 * Class Settings
 * Handles retrieving UI settings, prioritizing user cookies over config defaults.
 * Assumes cookie names are identical to the keys defined in $GLOBALS['config']['settings'].
 */
class Settings
{
    /** @var array Associative array of default settings from config.php */
    private $configDefaults = [];

    /** @var array Associative array of user cookies */
    private $cookies = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Ensure config is loaded globally or handle error/loading here if necessary
        if (!isset($GLOBALS['config']['settings'])) {
             $this->configDefaults = [];
        } else {
            $this->configDefaults = $GLOBALS['config']['settings'];
        }

        // Use $_COOKIE directly
        $this->cookies = $_COOKIE;
    }

    /**
     * Returns the raw default settings array as defined in config,
     * with appropriate type casting for JS consumption.
     * Useful for passing defaults to JavaScript.
     *
     * @return array
     */
    public function getAllDefaults(): array
    {
        $defaults = [];
        foreach ($this->configDefaults as $key => $value) {
            if (strpos($key, 'enable') === 0) { // Check if key starts with 'enable'
                $defaults[$key] = (bool) $value;
            } elseif (is_numeric($value)) { // Check if it's a numeric value
                $defaults[$key] = (int) $value;
            } else {
                // Keep other types as they are (e.g., strings)
                $defaults[$key] = $value;
            }
        }
        return $defaults;
    }

    /**
     * Gets the value for a numeric setting.
     * Priority: Cookie > Config. Returns 0 if not found in either.
     *
     * @param string $key The key used in config and as the cookie name.
     * @return int The setting value.
     */
    private function getNumericValue(string $key): int
    {
        if (isset($this->cookies[$key])) {
            return intval($this->cookies[$key]);
        }
        // Fallback to config default, or 0 if not defined in config
        return isset($this->configDefaults[$key]) ? (int)$this->configDefaults[$key] : 0;
    }

    /**
     * Determines if a boolean feature should be enabled.
     * Priority: Cookie > Config. Returns false if not found in either.
     * Assumes cookie value '1' means enabled.
     *
     * @param string $key The key used in config and as the cookie name ('enable...').
     * @return bool True if the feature should be considered enabled, false otherwise.
     */
    private function isFeatureEnabled(string $key): bool
    {
        if (isset($this->cookies[$key])) {
            // Cookie value '1' means enabled
            return ($this->cookies[$key] == 1);
        }
        // Fallback to config default, or false if not defined in config
        return isset($this->configDefaults[$key]) ? (bool)$this->configDefaults[$key] : false;
    }

    // --- Public Getters for Specific Settings ---
    // --- Uses the new unified config/cookie keys ---

    public function getTubePauseSeconds(): int
    {
        return $this->getNumericValue('tubePauseSeconds');
    }

    public function getAutoRefreshTimeoutMs(): int
    {
        return $this->getNumericValue('autoRefreshTimeoutMs');
    }

    public function getSearchResultLimit(): int
    {
        return $this->getNumericValue('searchResultLimit');
    }

    public function isAutoRefreshLoadEnabled(): bool
    {
        return $this->isFeatureEnabled('enableAutoRefreshLoad');
    }

    public function isJsonDecodeEnabled(): bool
    {
        return $this->isFeatureEnabled('enableJsonDecode');
    }

    public function isUnserializationEnabled(): bool
    {
        return $this->isFeatureEnabled('enableUnserialization');
    }

    public function isBase64DecodeEnabled(): bool
    {
        return $this->isFeatureEnabled('enableBase64Decode');
    }

    public function isJobDataHighlightEnabled(): bool
    {
        return $this->isFeatureEnabled('enableJobDataHighlight');
    }
}
