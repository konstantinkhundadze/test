<?php

namespace base\model;

class CustomerRegCardDetail extends ModelPDO {

    const CARD_STATUS_ACTIVE = 1;

    protected static $_entityClass = 'base\model\entity\CustomerCardDetail';

    public static $table = 'customer_reg_card_detail';

    public static $cardTypes = array(
        'VISA' => array(
            'title' => 'VISA Credit',
            'charge' => 3,
        ),
        'DELTA' => array(
            'title' => 'VISA Debit',
            'charge' => 0,
        ),
        'UKE' => array(
            'title' => 'VISA Electron',
            'charge' => 0,
        ),
        'MC' => array(
            'title' => 'MasterCard Credit',
            'charge' => 3,
        ),
        'MCDEBIT' => array(
            'title' => 'MasterCard Debit',
            'charge' => 0,
        ),
        'MAESTRO' => array(
            'title' => 'Maestro',
            'charge' => 0,
        ),
        'AMEX' => array(
            'title' => 'American Express',
            'charge' => 3,
        ),
    );

    /**
     * @param type $customerId
     * @return \base\model\entity\CustomerCardDetail || false
     */
    public function getByCustomerId($customerId)
    {
        $q = 'SELECT * FROM `' . self::$table . '` WHERE `customer_reg_id` = ? ORDER BY id DESC';
        $row = $this->findOne($q, $customerId);
        return $row ? $this->_toObject($row) : false;
    }

    /**
     * @param type $id
     * @return \base\model\entity\CustomerCardDetail || false
     */
    public function getById($id)
    {
        $q = 'SELECT * FROM `' . self::$table . '` WHERE `id` = ? ORDER BY id DESC';
        $row = $this->findOne($q, $id);
        return $row ? $this->_toObject($row) : false;
    }

    /**
     * @deprecated
     * @param type $customerId
     */
    public function get_card_details($customerId)
    {
        $row = $this->getByCustomerId($customerId);
        return $row ? $row->toArray() : false;
    }

    /**
     * @param type $card_detail
     * @return int
     */
    public function add_payment_card($card_detail)
    {
        return $this->insert($card_detail);
    }

    public static function getCardCharge($cardType)
    {
        return self::$cardTypes[$cardType]['charge'];
    }

    public function get_customer_billing_details($customerId)
    {
        $q = 'SELECT * FROM `customer_billing_details` WHERE customer_reg_id = ?';
        return $this->findOne($q, $customerId);
    }

    public function add_customer_billing_details($arrCustBDetails)
    {
        return $this->insert($arrCustBDetails, 'customer_billing_details');
    }

    public function update_card_detail($data, $cardId)
    {
        if ($card = $this->getById($cardId)) {
            return $card->load($data)->save();
        }
        return false;
    }

}
