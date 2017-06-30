<?php

namespace base\model;

use base\model\MySqlQueryBuilder;

class BookingPaymentRefund extends ModelPDO {
  
    protected static $_entityClass = 'base\model\entity\BookingPaymentRefund';
    public static $table = 'booking_payment_refund';

    public function getByBookingId($bookingId)
    {
        $sql = $this->select()->where('booking_id')->order('refund_id', MySqlQueryBuilder::SQL_DESC);
        $rows = $this->findRows($sql, $bookingId);
        return $this->_toCollection($rows);
    }

}
