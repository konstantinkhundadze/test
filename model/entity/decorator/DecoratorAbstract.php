<?php

namespace base\model\entity\decorator;

use base\model\entity\EntityInterface;

use base\helper\String as StringHelper;

class DecoratorAbstract {

    /**
     * @var EntityInterface 
     */
    protected $_primaryEntity;

    public function __construct(EntityInterface $entity)
    {
        $this->_primaryEntity = $entity;
    }

    public function __get($name)
    {
        if (property_exists($this->_primaryEntity, $name)) {
            return $this->_primaryEntity->{$name};
        }
        $method = 'get' . ucfirst(StringHelper::camelize($name));
        return $this->$method();
    }

}
