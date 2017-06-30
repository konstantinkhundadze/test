<?php

namespace base\model;

use base\helper\String;

class BookingTransaction extends ModelPDO {

    const BOOKING_STATUS_FAILED = 'failed';
    const BOOKING_STATUS_SUCCESS = 'success';

    public static $table = 'booking_transaction';

    public function findByVendorTxCode($txCode)
    {
        $row = $this->findOne('SELECT * FROM `' . self::$table .'` WHERE `VendorTxCode` = ? ', String::parseReference($txCode));
        return $row ? $this->_toObject($row) : false;
    }
}
