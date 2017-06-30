<?php

namespace base\helper;

use base\helper\Registry;
use base\model\CustomerRegAccount as Model;
use base\model\CustomerRegCardDetail;

/**
 * @todo method is dirty
 */
class Customer {

    private $_identity = null;
    private $_cardDetails = null;
    private $_passengerDetails = null;

    public static function init()
    {
        return new self();
    }

    /**
     * @return \base\helper\session\Customer || false
     */
    public function getIdentity()
    {
        $this->_identity = Registry::get(Registry::CUSTOMER_IDENTITY, null);
        if (null === $this->_identity) {
            $identity = false;
            if ($id = Registry::getApp()->getSession()->get('arrCustomer')->id) {
                $model = new Model();
                $identity = Registry::getApp()->getSession()->customer($model->find($id));
                Registry::set(Registry::CUSTOMER_IDENTITY, $identity);
            }
            $this->_identity = $identity;
        }
        return $this->_identity;
    }

    /**
     * @return \base\model\entity\CustomerCardDetail || false
     */
    public function getCardDetails()
    {
        if (null === $this->_cardDetails) {
            $cardModel = new CustomerRegCardDetail();
            $this->_cardDetails = $cardModel->getByCustomerId($this->getIdentity()->id);
        }
        return $this->_cardDetails;
    }

    /**
     * @todo entity object
     * @return array 
     */
    public function getPassengerDetails()
    {
        if (null === $this->_passengerDetails) {
            $model = new Model();
            $this->_passengerDetails = $model->get_customers_passenger_detail($this->getIdentity()->id);
        }
        return $this->_passengerDetails;
    }

}
