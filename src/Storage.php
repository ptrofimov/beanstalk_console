<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'IStorage.php';

class Storage implements IStorage {

    /**
     * eventually storage to be migrated on upgrade
     */
    const VERSION = '1';

    private $file;
    private $error;

    function __construct($config) {
        $this->file = $config;
        if (!$this->isAvailable()) {
            throw new InvalidArgumentException($this->error);
        }
    }

    public function isAvailable() {
        if (!is_file($this->file)) {
            if (!touch($this->file)) {
                $this->error = "Storage file could not be created. Please create the storage file manually, it must be writable: $this->file";
            }
            return false;
        }
        if (!is_writable($this->file)) {
            @chmod($this->file, 0755);
            if (!is_writable($this->file)) {
                $this->error = "Please make the storage file writable: $this->file";
                return false;
            }
        }
        return true;
    }

    public function getError() {
        return $this->error;
    }

    public function saveJob($arr) {
        if ($this->validate($arr) && $this->save($arr)) {
            return true;
        } else {
            return false;
        }
    }

    protected function validate($arr) {
        //job exists
        $collection = $this->readCollection();
        if (isset($collection[self::VERSION]['jobs'][md5($arr['name'])])) {
            $this->error = 'You already have a job with this name';
            return false;
        }
        return true;
    }

    protected function save($arr) {
        $collection = $this->readCollection();
        $collection[self::VERSION]['jobs'][md5($arr['name'])] = $arr;
        if (is_array($arr['tubes'])) {
            foreach ($arr['tubes'] as $tubename => $val) {
                $collection[self::VERSION]['tubes'][$tubename][md5($arr['name'])] = '';
            }
        }
        $this->writeCollection($collection);
        return true;
    }

    protected function readCollection() {
        $json = file_get_contents($this->file);
        return json_decode($json, true);
    }

    protected function writeCollection($collection) {
        file_put_contents($this->file, json_encode($collection));
    }

    public function getJobs() {
        $collection = $this->readCollection();
        return @$collection[self::VERSION]['jobs'];
    }

    public function getJobsForTube($tubename) {
        $collection = $this->readCollection();
        $result = array();
        if (@is_array($collection[self::VERSION]['tubes'][$tubename])) {
            foreach ($collection[self::VERSION]['tubes'][$tubename] as $key => $val) {
                $job = @$collection[self::VERSION]['jobs'][$key];
                if (!empty($job)) {
                    $result[$key] = $job['name'];
                }
            }
        }
        return $result;
    }

    public function load($key) {
        $collection = $this->readCollection();
        if (isset($collection[self::VERSION]['jobs'][$key])) {
            return $collection[self::VERSION]['jobs'][$key];
        }
        return false;
    }

    public function delete($key) {
        $collection = $this->readCollection();
        if (isset($collection[self::VERSION]['jobs'][$key])) {
            if (is_array($collection[self::VERSION]['jobs'][$key]['tubes'])) {
                foreach ($collection[self::VERSION]['jobs'][$key]['tubes'] as $tubename => $val) {
                    unset($collection[self::VERSION]['tubes'][$tubename][$key]);
                }
            }
            unset($collection[self::VERSION]['jobs'][$key]);
            $this->writeCollection($collection);
        }
        return false;
    }

}
