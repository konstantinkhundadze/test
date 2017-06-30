<?php

namespace base\view\helper;

class DataStorage extends \IteratorIterator {
    
    public function __construct()
    {
        parent::__construct($this->getIterator());
    }
    
    public function getIterator() {
        return new \ArrayIterator($this);
    }

    public function toArray()
    {
        return call_user_func('get_object_vars', $this);
    }
}
