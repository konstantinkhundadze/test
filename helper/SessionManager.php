<?php

namespace base\helper;

/**
 * @todo $this->get() must thrown exception if Key not exist
 * @todo $this->_getEntity() must thrown exception if Helper not exist
 *
 * @example
 *      $this->myapp->getSession()->booking(array('amount' => 123));
 *      var_dump($this->myapp->getSession()->booking()->toArray());
 */
class SessionManager {

    protected $_entityStore = array();

    public static function init()
    {
        $instance = new self();
        if(is_array($_SESSION)){
          foreach ($_SESSION as $k => $v) {
              if (is_array($v)) {
                  $instance->_getEntity($k, $v);
              }
          }
        }
        return $instance;
    }

    public function __call($name, $arguments = array())
    {
        return $this->_getEntity($name, isset($arguments[0]) ? $arguments[0] : array());
    }

    public function __set($key, $value = array())
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __destruct()
    {
        foreach ($this->_entityStore as $k => $v) {
            $this->_setData($k, $v->toArray());
        }
    }

    public function started()
    {
        return $this->getId() != '';
    }

    public function exists($key)
    {
        if ($this->started() == false) {
            session_start();
        }
        return isset($this->_entityStore[$key]);
    }

    public function set($key, $value = array())
    {
        $this->_getEntity($key, $value);
        return $this;
    }

    public function get($key, $default = null)
    {
        if (!isset($this->_entityStore[$key]) && isset($_SESSION[$key])) {
            $this->set($key, $_SESSION[$key]);
        }
        return isset($this->_entityStore[$key]) ? $this->_entityStore[$key] : $default;
    }

    public function clear($key)
    {
        unset($this->_entityStore[$key]);
        unset($_SESSION[$key]);
        return $this;
    }

    protected function _setData($key, $value = array())
    {
        if(!$this->started()) {
            session_start();
        }
        $_SESSION[$key] = $value;
        return $this;
    }

    protected function _getEntity($key, $data = array())
    {
        if (!isset($this->_entityStore[$key])) {
            $namespace = '\\base\\helper\\session\\';
            $mapperName = class_exists($namespace . ucfirst($key)) ? $namespace . ucfirst($key) : $namespace . 'SessionBase';
            $this->_entityStore[$key] = new $mapperName($data);
            $this->_setData($key, $this->_entityStore[$key]->toArray());
        } elseif ($data) {
            $this->_setData($key, $this->_entityStore[$key]->load($data)->toArray());
        }
        return $this->_entityStore[$key];
    }

    public function getId()
    {
        return session_id();
    }

}
