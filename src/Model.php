<?php
class Model
{
    private static $registry = array();

    public static function create()
    {
        return new static();
    }

    public static function find()
    {

    }

    public function remove()
    {

    }

    public function __get($key)
    {
        if (!method_exists($this, 'get' . $key)) {
            throw new BadMethodCallException("No get access for property $key");
        }

        return $this->{'get' . $key}();
    }

    public function __set($key, $value)
    {
        if (!method_exists($this, 'set' . $key)) {
            throw new BadMethodCallException("No set access for property $key");
        }

        return $this->{'set' . $key}($value);
    }
}
