<?php

namespace base\helper;

class RegistryAbstract {

    static protected $_data = array();

    static protected $_lock = array();

    static public function set($key, $value)
    {
        if (!static::hasLock($key)) {
            static::$_data[$key] = $value;
        } else {
            throw new \Exception("Variable '$key' is locked for editing");
        }
    }

    static public function get($key, $default = null)
    {
        if (static::has($key)) {
            return static::$_data[$key];
        } else {
            return $default;
        }
    }

    static public function remove($key)
    {
        if (static::has($key) && static::hasLock($key)) {
            unset(static::$_data[$key]);
        }
    }

    static public function has($key)
    {
        return isset(static::$_data[$key]);
    }

    static public function lock($key)
    {
        static::$_lock[$key] = true;
    }

    static public function hasLock($key)
    {
        return isset(static::$_lock[$key]);
    }

    static public function unlock($key)
    {
        if (static::hasLock($key)) {
            unset(static::$_lock[$key]);
        }
    }

}
