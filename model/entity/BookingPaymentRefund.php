<?php

namespace base\model\entity;

class BookingPaymentRefund extends EntityBase {

    protected static $_modelName = 'base\model\BookingPaymentRefund';
    public static $primaryKey = 'refund_id';

    public $refund_id;
    public $payment_id;
    public $booking_id;
    public $refund_dts;
    public $refund_amount;
    public $refund_user_id;
    public $refund_comment;
    public $refund_sagepay_transaction_id;
    public $insert_sagepay_fields;

}
