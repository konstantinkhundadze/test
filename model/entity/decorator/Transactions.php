<?php

namespace base\model\entity\decorator;

use base\model\Transactions as Model;
use base\model\Notification as NotificationModel;
use base\model\Customer as CustomerModel;
use base\model\Address as AddressModel;
use base\model\Mco as McoModel;
use base\model\Driver as DriverModel;
use base\model\DriverNotificationAnswers as DnaModel;
use base\model\PromotionalCode as PromoCodeModel;
use base\model\Affiliate as AffiliateModel;
use base\model\BookingPaymentRefund as PaymentRefundModel;

use base\helper\transaction\RevenueCalculator;
use base\helper\transaction\AnalyseTrip;

class Transactions extends DecoratorAbstract {

    /**
     * @var \base\model\entity\Customer
     */
    private $_customer;

    /**
     * @var \base\model\entity\Mco
     */
    private $_mco;

    /**
     * @var \base\model\entity\Driver
     */
    private $_driver;
    
    /**
     * @var array \base\model\entity\DriverNotificationAnswers collection
     */
    private $_driverStatusLog;

    /**
     * @var RevenueCalculator 
     */
    private $_revenue;
    
    /**
     * @var RevenueCalculator 
     */
    private $_promo_code;

    /**
     * @var array \base\model\entity\Address collection
     */
    private $_via_addresses;

    /**
     * @var \base\model\entity\Address 
     */
    private $_pickup_address;

    /**
     * @var \base\model\entity\Address 
     */
    private $_destination_address;

    /**
     * @var string 
     */
    private $_stage;

    /**
     * @var string 
     */
    private $_stageQuestion;
    
    /**
     * @var string 
     */
    private $_analiseCustomer;
    
    /**
     * @var string 
     */
    private $_analyseTrip;

    /**
     * @var string 
     */
    private $_affiliate;

    /**
     * @var base\model\entity\Transactions 
     */
    private $_inOutBound;

    /**
     * @var base\model\entity\BookingPaymentRefund 
     */
    private $_paymentRefund;

    /**
     * Generate Payment Type
     */
    public function getPaymentLabel()
    {
        $labels = array(
            'card' => 'PRE-PAID BY CARD',
            'cash' => 'PAID BY CASH',
            'pingit' => 'PRE-PAID BY PINGIT',
            'paypal' => 'PRE-PAID BY PAYPAL'
        );
        return $labels[$this->payment_type];
    }

    /**
     * Supplement with customer details
     * @return \base\model\entity\decorator\Customer
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $model = new CustomerModel();
            $this->_customer = $model->find($this->customer_id)->decorate();
        }
        return $this->_customer;
    }

    /**
     * @todo
     */
    public function getStageQuestion()
    {
        if (is_null($this->_stageQuestion)) {
            $this->_stageQuestion = new \stdClass();
            $status = $this->driver_notification_status;

            if ($driverStatusLog = $this->getDriverStatusLog()) {
                $driverStatus = end($driverStatusLog);
                $status = $driverStatus->driver_answer;
            }

            foreach (NotificationModel::$driver_notification_status_byStage as $key => $value) {
                if (in_array($status, $value)) {
                    $this->_stageQuestion->ask_stage = $key;
                    $this->_stageQuestion->question = NotificationModel::$driver_options_statuses[$key - 1]['question'];
                    $this->_stageQuestion->answer = NotificationModel::$driver_options_statuses[$key - 1]['options'][$status] 
                            ? NotificationModel::$driver_options_statuses[$key - 1]['options'][$status] 
                            : "not yet set ";
                }
            }
        }
        return $this->_stageQuestion;
    }

    /**
     * Supplement with addresses
     * @return \base\model\entity\Address
     */
    public function getPickupAddress()
    {
        if (is_null($this->_pickup_address)) {
            $model = new AddressModel();
            $this->_pickup_address = $model->find($this->pickup_address_id);
        }
        return $this->_pickup_address;
    }

    /**
     * Supplement with addresses
     * @return \base\model\entity\Address
     */
    public function getDestinationAddress()
    {
        if (is_null($this->_destination_address)) {
            $model = new AddressModel();
            $this->_destination_address = $model->find($this->destination_address_id);
        }
        return $this->_destination_address;
    }

    /**
     * Supplement with addresses
     * @return array \base\model\entity\Address collection
     */
    public function getViaAddresses()
    {
        if (is_null($this->_via_addresses)) {
            $model = new AddressModel();
            $this->_via_addresses = $model->findManyByIds($this->via_address_ids);
        }
        return $this->_via_addresses;
    }

    /**
     * @return \base\model\entity\Driver
     */
    public function getDriver()
    {
        if (is_null($this->_driver) && $this->driver_id) {
            $model = new DriverModel();
            $this->_driver = $model->find($this->driver_id);
        }
        return $this->_driver;
    }

    /**
     * @return \base\model\entity\decorator\Mco
     */
    public function getMco()
    {
        if (is_null($this->_mco)) {
            $model = new McoModel();
            $this->_mco = $model->find($this->mco_id)->decorate();
        }
        return $this->_mco;
    }

    /**
     * @todo
     * @return string
     */
    public function getStage()
    {
        if (is_null($this->_stage)) {
            $model = new Model();
            $row = $model->get_ask_bookings(null, 1, 0, $this->booking_linktext);
            $this->_stage = $row['stage_number'];
        }
        return $this->_stage;
    }

    /**
     * Generate Booking revenue
     * @return base\helper\transaction\RevenueCalculator;
     */
    public function getRevenue()
    {
        if (is_null($this->_revenue)) {
            $this->_revenue = new RevenueCalculator($this);
        }
        return $this->_revenue;
    }

    /**
     * @return array \base\model\entity\DriverNotificationAnswers collection
     */
    public function getDriverStatusLog()
    {
        if (is_null($this->_driverStatusLog)) {
            $model = new DnaModel();
            $this->_driverStatusLog = $model->getByTransactionMco($this->booking_linktext, $this->mco_id);
        }
        return $this->_driverStatusLog;
    }

    /**
     * @return \base\model\entity\PromotionalCode
     */
    public function getPromoCode()
    {
        if ($this->promo_code_id && is_null($this->_promo_code)) {
            $model = new PromoCodeModel();
            $this->_promo_code = $model->find($this->promo_code_id);
        }
        return $this->_promo_code;
    }

    public function getAnaliseCustomer()
    {
        if (is_null($this->_analiseCustomer)) {
            $model = new Model();
            $this->_analiseCustomer = $model->analyseCustomer($this->customer_id);
        }
        return $this->_analiseCustomer;
    }
    
    public function getAnalyseTrip()
    {
        if (is_null($this->_analyseTrip)) {
            $this->_analyseTrip = AnalyseTrip::flag($this);
        }
        return $this->_analyseTrip;
    }

    /**
     * @return \base\model\entity\Affiliate
     */
    public function getAffiliate()
    {
        if ($this->affiliate_id && is_null($this->_affiliate)) {
            $model = new AffiliateModel();
            $this->_affiliate = $model->find($this->affiliate_id);
        }
        return $this->_affiliate;
    }

    /**
     * @var \base\model\entity\Transactions 
     */
    public function getInOutBound()
    {
        if (is_null($this->_inOutBound) && ($this->type == Model::TRIP_TYPE_INBOUND || $this->type == Model::TRIP_TYPE_OUTBOUND)) {
            $tripType = ($this->type == Model::TRIP_TYPE_INBOUND) ? Model::TRIP_TYPE_OUTBOUND : Model::TRIP_TYPE_INBOUND;
            $model = new Model();
            $this->_inOutBound = $model->getByVendorTxCode($this->VendorTxCode, $tripType);
        }
        return $this->_inOutBound;
    }

    /**
     * @var \base\model\entity\BookingPaymentRefund 
     */
    public function getPaymentRefund()
    {
        if (is_null($this->_paymentRefund)) {
            $model = new PaymentRefundModel();
            $this->_paymentRefund = $model->getByBookingId($this->booking_id);
        }
        return $this->_paymentRefund;
    }
}
