<?php

/**
 * Stores per-server, per-tube body display overrides.
 */
class TubeBodyDisplayStorage {

    const VERSION = 1;

    private $file;

    /**
     * Builds storage around the configured path, defaulting beside storage.json.
     *
     * @param array $config Application config.
     */
    public function __construct($config) {
        if (isset($config['tubeBodyDisplay']['storage']) && $config['tubeBodyDisplay']['storage'] !== '') {
            $this->file = $config['tubeBodyDisplay']['storage'];
        } else {
            $this->file = dirname($config['storage']) . DIRECTORY_SEPARATOR . 'tube-body-display.json';
        }
    }

    /**
     * Returns the configured storage file path.
     *
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Returns an override for a server/tube pair when one exists.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @return array|null
     * @throws InvalidArgumentException If the storage file cannot be read.
     */
    public function getOverride($server, $tube) {
        $data = $this->read();
        if (isset($data['servers'][$server][$tube]) && is_array($data['servers'][$server][$tube])) {
            return $data['servers'][$server][$tube];
        }
        return null;
    }

    /**
     * Saves an override for a server/tube pair.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @param array $settings Body display settings.
     * @return void
     * @throws InvalidArgumentException If the storage file cannot be written.
     */
    public function saveOverride($server, $tube, $settings) {
        $data = $this->read();
        if (!isset($data['servers']) || !is_array($data['servers'])) {
            $data['servers'] = array();
        }
        if (!isset($data['servers'][$server]) || !is_array($data['servers'][$server])) {
            $data['servers'][$server] = array();
        }
        $data['servers'][$server][$tube] = $settings;
        $this->write($data);
    }

    /**
     * Removes an override for a server/tube pair.
     *
     * @param string $server Server connection string.
     * @param string $tube Tube name.
     * @return void
     * @throws InvalidArgumentException If the storage file cannot be written.
     */
    public function deleteOverride($server, $tube) {
        $data = $this->read();
        if (isset($data['servers'][$server][$tube])) {
            unset($data['servers'][$server][$tube]);
            if (!count($data['servers'][$server])) {
                unset($data['servers'][$server]);
            }
            $this->write($data);
        }
    }

    /**
     * Reads the storage document.
     *
     * @return array
     * @throws InvalidArgumentException If the storage file cannot be read.
     */
    private function read() {
        if (!is_file($this->file)) {
            return $this->defaultDocument();
        }
        if (!is_readable($this->file)) {
            throw new InvalidArgumentException('Tube body display settings file is not readable: ' . $this->file);
        }

        $contents = file_get_contents($this->file);
        if ($contents === false || trim($contents) === '') {
            return $this->defaultDocument();
        }

        $data = json_decode($contents, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Tube body display settings file is not valid JSON: ' . $this->file);
        }
        if (!isset($data['version'])) {
            $data['version'] = self::VERSION;
        }
        if (!isset($data['servers']) || !is_array($data['servers'])) {
            $data['servers'] = array();
        }

        return $data;
    }

    /**
     * Writes the storage document with an exclusive lock.
     *
     * @param array $data Storage document.
     * @return void
     * @throws InvalidArgumentException If the storage file cannot be written.
     */
    private function write($data) {
        $dir = dirname($this->file);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new InvalidArgumentException('Tube body display settings directory is not writable: ' . $dir);
        }

        $handle = fopen($this->file, 'c+');
        if (!$handle) {
            throw new InvalidArgumentException('Tube body display settings file could not be opened: ' . $this->file);
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new InvalidArgumentException('Tube body display settings file could not be locked: ' . $this->file);
            }
            if (!ftruncate($handle, 0) || !rewind($handle)) {
                throw new InvalidArgumentException('Tube body display settings file could not be prepared: ' . $this->file);
            }
            $encoded = json_encode($data, JSON_PRETTY_PRINT);
            if ($encoded === false || fwrite($handle, $encoded . "\n") === false) {
                throw new InvalidArgumentException('Tube body display settings file could not be written: ' . $this->file);
            }
            fflush($handle);
            flock($handle, LOCK_UN);
        } catch (Exception $e) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw $e;
        }

        fclose($handle);
    }

    /**
     * Returns an empty storage document.
     *
     * @return array
     */
    private function defaultDocument() {
        return array(
            'version' => self::VERSION,
            'servers' => array(),
        );
    }
}
