<?php

namespace base\model\entity;

class EntityEmpty extends EntityBase {

    protected $_readOnly = true;

    public function __construct($data = array())
    {
        parent::__construct();
        if ($data) {
            $this->load($data);
        }
    }

    public function load($data)
    {
        foreach ($data as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }

}
