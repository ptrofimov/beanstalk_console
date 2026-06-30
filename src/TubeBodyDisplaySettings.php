<?php

/**
 * Resolves effective body display settings for a server/tube pair.
 */
class TubeBodyDisplaySettings {

    private static $keys = array(
        'enableBase64Decode',
        'enableUnserialization',
        'enableJsonDecode',
    );

    private $settings;
    private $storage;

    /**
     * Stores global settings and optional tube override storage.
     *
     * @param Settings|null $settings Global UI settings.
     * @param TubeBodyDisplayStorage|null $storage Tube override storage.
     */
    public function __construct($settings = null, $storage = null) {
        $this->settings = $settings ?: new Settings();
        $this->storage = $storage;
    }

    /**
     * Returns the effective display settings for a server/tube pair.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @return array Effective settings plus metadata.
     * @throws InvalidArgumentException If override storage cannot be read.
     */
    public function getEffectiveSettings($server, $tube) {
        $global = $this->getGlobalSettings();
        $override = $this->getOverride($server, $tube);
        if ($override !== null) {
            return array_merge(
                $this->normalizeSettings($override, $global),
                array('source' => 'tube', 'has_override' => true)
            );
        }

        return array_merge($global, array('source' => 'global', 'has_override' => false));
    }

    /**
     * Returns the configured tube override when present.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @return array|null
     * @throws InvalidArgumentException If override storage cannot be read.
     */
    public function getOverride($server, $tube) {
        if (!$this->storage || $server === '' || $tube === '') {
            return null;
        }
        return $this->storage->getOverride($server, $tube);
    }

    /**
     * Saves a custom tube override.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @param array $settings Body display settings.
     * @return void
     * @throws InvalidArgumentException If override storage cannot be written.
     */
    public function saveOverride($server, $tube, $settings) {
        $this->storage->saveOverride($server, $tube, $this->normalizeSettings($settings));
    }

    /**
     * Removes a custom tube override.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @return void
     * @throws InvalidArgumentException If override storage cannot be written.
     */
    public function deleteOverride($server, $tube) {
        $this->storage->deleteOverride($server, $tube);
    }

    /**
     * Returns global cookie/config body display settings.
     *
     * @return array
     */
    public function getGlobalSettings() {
        return array(
            'enableBase64Decode' => $this->settings->isBase64DecodeEnabled(),
            'enableUnserialization' => $this->settings->isUnserializationEnabled(),
            'enableJsonDecode' => $this->settings->isJsonDecodeEnabled(),
        );
    }

    /**
     * Returns only body display setting keys.
     *
     * @return array
     */
    public static function getSettingKeys() {
        return self::$keys;
    }

    /**
     * Normalizes boolean settings and applies fallback values when supplied.
     *
     * @param array $settings Candidate settings.
     * @param array $fallback Optional fallback settings.
     * @return array
     */
    public function normalizeSettings($settings, $fallback = array()) {
        $normalized = array();
        foreach (self::$keys as $key) {
            if (isset($settings[$key])) {
                $normalized[$key] = (bool)$settings[$key];
            } elseif (isset($fallback[$key])) {
                $normalized[$key] = (bool)$fallback[$key];
            } else {
                $normalized[$key] = false;
            }
        }
        return $normalized;
    }
}
