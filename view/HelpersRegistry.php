<?php

namespace base\view;

use base\helper\Registry;

use base\view\helper\HelperInterface;
use base\exception\InvalidParamException;

class HelpersRegistry {

    /**
     * @var array the helpers in this collection
     */
    private $_helpers = array();

    /**
     * @todo helpers from instances
     * @param type $helperName
     * @param type $helperAlias
     * @return base\view\helper\HelperInterface
     * @throws InvalidParamException
     */
    public function getHelper($helperName, $helperAlias = '')
    {
        $app = Registry::getApp();
        
        $helperClass = '\\'.$app->getInstanceName().'\\mvc\\view\helper\\'.ucfirst($helperName);
        if (!class_exists($helperClass)) {
            $helperClass = 'base\view\helper\\'.ucfirst($helperName);
        }

        $key = $helperAlias ?: $helperClass;

        if (!$this->has($key)) {
            if (!class_exists($helperClass)) {
                throw new InvalidParamException('Helper "'.$helperClass.'" not found');
            }
            $this->set($key, new $helperClass());
        }
        return $this->get($key);
    }

    public function __call($helperName, $arguments)
    {
        $helperAlias = '';
        if (!empty($arguments) && !empty($arguments[0]) && is_string($arguments[0])) {
            $helperAlias = $arguments[0];
        }
        return $this->getHelper($helperName, $helperAlias);
    }

    /**
     * Returns the named helper
     * @param string $key the name of the helper to return
     */
    public function get($key)
    {
        return $this->_helpers[$key];
    }

    /**
     * Adds a new helper.
     */
    public function set($key, HelperInterface $helperClass)
    {
        $this->_helpers[$key] = $helperClass;
        return $this;
    }

    /**
     * Returns a value indicating whether the named helper exists.
     */
    public function has($key)
    {
        return isset($this->_helpers[$key]);
    }

}
