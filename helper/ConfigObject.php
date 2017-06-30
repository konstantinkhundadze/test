<?php

namespace base\helper;

use base\exception\InvalidConfigException;

/**
 * 
 */
class ConfigObject {
    
    private $_vars = array();
    private $_locket = array();

    public function toArray()
    {
        return $this->_vars;
    }

    public function __get($name)
    {
        return $this->_vars[$name];
    }

    public function __set($name, $value)
    {
        if (isset($this->_locket[$name])) {
            throw new InvalidConfigException('You cannot change the configuration at runtime');
        } else {
            $this->_vars[$name] = $value;
            $this->_locket[$name] = true;
        }

        return $this;
    }
}
