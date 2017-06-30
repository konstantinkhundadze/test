<?php

namespace base\model\entity\decorator;

use base\model\Address as AddressModel;

class Customer extends DecoratorAbstract {

    /**
     * @var AddressModel 
     */
    private $_address;

    /**
     * @return \base\model\entity\Address
     */
    public function getAddress()
    {
        if ($this->pickup_address_id && is_null($this->_address)) {
            $model = new AddressModel();
            $this->_address = $model->find($this->pickup_address_id);
        }
        return $this->_address;
    }

}
