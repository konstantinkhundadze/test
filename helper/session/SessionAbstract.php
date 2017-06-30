<?php

namespace base\helper\session;

use base\model\entity\EntityInterface;

abstract class SessionAbstract {
    
    protected $_single = false;

    protected $_value;

    public function __construct($data = array())
    {
        $this->load($data);
    }

    /**
     * @param mixed $data array || base\model\entity\EntityInterface
     * @return \base\helper\session\SessionAbstract
     */
    public function load($data)
    {
        if ($data instanceof EntityInterface) {
            $data = $data->toArray();
        }

        $vars = call_user_func('get_object_vars', $this);
        foreach($vars as $k => $v) {
            if (isset($data[$k]) && $this->isValid($k, $v)) {
                $this->{$k} = $data[$k];
            }
        }
        return $this;
    }

    public function toArray()
    {
        if ($this->_single) {
            return array('value' => $this->_value);
        }

        return call_user_func('get_object_vars', $this);
    }

    public function toString()
    {
        return $this->_value;
    }

    public function isValid($k, $v)
    {
        return true;
    }

}
