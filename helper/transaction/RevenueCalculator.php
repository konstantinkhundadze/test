<?php

namespace base\helper\transaction;

use base\model\McoWeeklyStatements;
use base\model\Transactions as Model;

use base\model\entity\decorator\Transactions as Entity;

use base\helper\Vat;

class RevenueCalculator {

    private $_transaction;

    public $minicabit_commition;
    public $collect_from_customer;
    public $booking_fees;
    public $mco_retain;
    public $vat_commission;
    public $minicabit_retain;
    public $fare_before_deductions;
    public $payment_status = 'PENDING';
    public $payment_color = 'red';

    public function __construct(Entity $transaction)
    {
        $this->_transaction = $transaction;
        $this->_vat();
        $this->_paymentStatus();
    }

    private function _vat()
    {
        $tr = $this->_transaction;

        $tripCharges = $tr->trip_charges;
        if (($promoCode = $tr->getPromoCode()) && ($discount = $promoCode->calculateDiscount($tr->gross_amount))) {
            if ($tr->type == Model::TRIP_TYPE_INBOUND || $tr->type == Model::TRIP_TYPE_OUTBOUND) {
                $discount = $discount / 2;
            }
            $tripCharges += $discount;
        }

        $vatRules = Vat::getVatRules($this->pickup_eta_dts);
        $this->minicabit_commition = sprintf("%01.2f", $tripCharges * $vatRules->vatRetain);
        $this->collect_from_customer = $tr->amount;
        $this->booking_fees = $tr->booking_fees;
        $this->mco_retain = $tripCharges - $this->minicabit_commition;
        $this->vat_commission = $this->trip_charges * $vatRules->vatCommission;
        $this->minicabit_retain = sprintf("%01.2f", $this->minicabit_commition + $tr->booking_fees);
        $this->collect_from_customer = $tr->amount;
        $this->fare_before_deductions = $tripCharges;
    }

    private function _paymentStatus()
    {
        $tr = $this->_transaction;
        $statementModel = new McoWeeklyStatements();
        $time = $tr->booking_completed_dts ?: $tr->pickup_eta_dts;
        $statement = $statementModel->getStatmentStatus($time, $tr->mco_id);

        $cashPendingArr = array(
            Model::DRIVER_NOTICE_STATUS_POB_4,
            Model::DRIVER_NOTICE_STATUS_POB_3,
            Model::DRIVER_NOTICE_STATUS_ASK_LIVE,
            Model::DRIVER_NOTICE_STATUS_LIVE,
            Model::DRIVER_NOTICE_STATUS_NOT_DRIVER_3,
            Model::DRIVER_NOTICE_STATUS_NOT_DRIVER_4
        );
        $cardPendingArr = array(
            Model::DRIVER_NOTICE_STATUS_POB_4,
            Model::DRIVER_NOTICE_STATUS_POB_3,
            Model::DRIVER_NOTICE_STATUS_NOT_DRIVER_3,
            Model::DRIVER_NOTICE_STATUS_NOT_DRIVER_4
        );

        if ($statement->status == 'paid') {
            if ($tr->payment_type != Model::PAMENT_TYPE_CASH) {
                $this->payment_status = 'PAID';
                $this->payment_color = 'green';
            } else {
                $this->payment_status = 'RECONCILED';
                $this->payment_color = 'green';
            }
        }

        if ($tr->booking_status == 'cancelled') {
            $this->payment_status = 'CANCELLED';
            $this->payment_color = 'red';
        }
    }

}
