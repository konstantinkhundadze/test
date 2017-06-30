<?php

namespace base\helper;

use base\model\Booking as BookingModel;
use base\helper\String as StringHelper;
use base\helper\transaction\VatRulesObject;

class Vat {

    /**
     * @todo
     * @param string $linktext
     * @return array booking details
     */
    public static function handleRef($linktext)
    {
        $model = new BookingModel();
        $booking = $model->findByLinktext($linktext);
        return $booking ? $model->get_booking_details(StringHelper::parseReference($booking['VendorTxCode']), $booking['booking_id']) : array();
    }

    /**
     * @param string $time timestamp
     * @return VatRulesObject
     */
    public static function getVatRules($time)
    {
        $rules = new VatRulesObject();
        $rules->vatFlag = $time ? ((strtotime('2015-06-15') - 60) < strtotime($time)) : ((strtotime('2015-06-15') - 60) < time());
        $rules->totalCommission = $rules->vatFlag ? 0.12 : 0.1;
        $rules->vatCommission = $rules->vatFlag ? 0.02 : 0;
        $rules->vatOnAdj = $rules->vatFlag ? 0.2 : 0;
        $rules->vatRetain = 1 - $rules->totalCommission;
        $rules->splitAdj = $rules->vatFlag ? 1.2 : 1;
        $rules->vatPdf = $rules->vatFlag ? 0.16666667 : 0;
        return $rules;
    }

}
