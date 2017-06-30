<?php

namespace base\helper;

use base\helper\Registry;
use base\helper\Customer;

require_once __DIR__ . '/Braintree/Braintree.php';

class Braintree {

    const DEFAULT_MESSAGE = 'The card details you entered are incorrect, please try again.';

    const DEFAULT_ERROR = 'Unknown error';

    const PAYMENT_SUCCESS = 'The transaction was successfully authorised with the bank.';

    private static $_app;

    private $_response;

    private $_errors = array();

    /**
     * @todo
     * @param string $token
     */
    public function findCardByToken($token)
    {
        $card = \Braintree_CreditCard::find($token);
        dd($card);
    }
    public function __construct()
    {
        self::_setup();
    }

    /**
     * @return Braintree instance
     */
    public static function init()
    {
        return new self();
    }

    private static function _setup()
    {
        \Braintree_Configuration::environment(self::environment());
        \Braintree_Configuration::merchantId(self::merchantId());
        \Braintree_Configuration::publicKey(self::publicKey());
        \Braintree_Configuration::privateKey(self::privateKey());
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getErrorMessage()
    {
        $message = '';
        if (isset($this->_errors['braintree_code'])) {
            $message = $this->_prepareErrorMessage();
        } else {
            foreach ($this->_errors as $value) {
                $message = $value;
                break;
            }
        }
        if ($this->_errors) {
            $logger = Registry::getLogger('Braintree');
            $logger->debug('$this->_errors : "' . var_export($this->_errors, true) . "'");
            $logger->debug('LOG end for Braintree');
        }
        return $message;
    }

    private function _prepareErrorMessage()
    {
        $email = Registry::getApp()->getConfig()->emails->support;
        $messages = array(
            2014 => 'Your booking has not been completed. Please email '.$email.' quoting error number 8008',
            'Gateway Rejected: fraud' => 'Your booking has not been completed. Please email '.$email.' quoting error number 8008'
        );
        if (isset($messages[$this->_errors['braintree_message']])) {
            return $messages[$this->_errors['braintree_message']];
        } elseif (isset($messages[$this->_errors['braintree_code']])) {
            return $messages[$this->_errors['braintree_code']];
        }
        return $this->_errors['braintree_message'];
    }

    private function _isValidResponse()
    {
        if ($this->_response->success) {
            return true;
        } else if ($this->_response->transaction) {
            $this->_errors = array(
                'braintree_message' => $this->_response->message,
                'braintree_code' => $this->_response->transaction->processorResponseCode,
                'braintree_text' => $this->_response->transaction->processorResponseText
            );
        } elseif (!empty($this->_response->message)) {
            $this->_errors = array(
                'braintree_message' => $this->_response->message,
            );
            foreach($this->_response->errors->deepAll() AS $error) {
                $this->_errors['braintree_code'] = $error->code;
                $this->_errors['braintree_text'] = $error->message;
                break;
            }
        } else {
            $this->_errors = array(
                'braintree_message' => self::DEFAULT_MESSAGE,
                'braintree_code' => 0,
                'braintree_text' => self::DEFAULT_ERROR,
            );
        }
        return false;
    }

    /**
     * @param int $customerId Braintree customer_id
     * @param float $amount
     * @param string $orderId
     * @param string $deviceData JSON
     * @param string $nonce nonce-from-the-client
     * 
     * @return boolean $this->_isValidResponse()
     */
    public function sendPayment($customerId, $amount, $orderId, $deviceData, $nonce = null, $cvv = null)
    {
        if(self::app()->getConfig()->braintree->environment == 'sandbox') {
            $nonce = 'fake-valid-nonce';
        }

        $params = array(
            'customerId' => $customerId,
            'amount' => round($amount,2),
            'options' => array(
                'submitForSettlement' => true
            ),
            'deviceData' => $deviceData,
            'orderId' => $orderId,
        );

        if ($nonce) {
            $params['paymentMethodNonce'] = $nonce;
        }

        if ($cvv) {
            $params['creditCard'] = array();
            $params['creditCard']['cvv'] = $cvv;
        }

        $this->_response = \Braintree_Transaction::sale($params);

        if (self::app()->getConfig()->debug->flag) {
            $model = new \base\model\ModelPDO();
            $model->insert(
                array(
                    'order_id' => $orderId,
                    'customer_id' => $customerId,
                    'amount' => round($amount,2),
                    'token' => self::app()->getSession()->braintree()->token,
                    'nonce_from_client' => $nonce,
                    'device_data' => $deviceData,
                    'responce' => var_export($this->_response, true)
                ), 
                'log_braintre_transaction'
            );
        }
        return $this->_isValidResponse();
    }

    public function getCustomer($customerId)
    {
        try {
            return \Braintree_Customer::find($customerId);
        } catch (\Braintree_Exception_NotFound $e) {
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function createCustomer($data)
    {
        $this->_validateCardData($data);
        if ($this->_errors) {
            return false;
        }
        $this->_response = \Braintree_Customer::create(array(
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['mobile_num'],
            'creditCard' => array(
                'number' => $data['number'],
                'expirationMonth' => $data['expirationMonth'],
                'expirationYear' => $data['expirationYear'],
                'cvv' => $data['cvv'],
                'cardholderName' => $data['cardholderName'],
                'billingAddress' => array(
                    'postalCode' => $data['postal_code'],
                    'streetAddress' => $data['streetAddress'],
                    'extendedAddress' => $data['extendedAddress'],
                    'locality' => $data['locality'],
                    'countryCodeAlpha2' => $data['countryCodeAlpha2'],
                )
            )
        ));
        return $this->_isValidResponse();
    }

    /**
     * @param int $customerId Braintree customer id
     * @param array $data
     * @return bool
     */
    public function updateCustomer($customerId, $data)
    {
        $this->_validateCardData($data);
        if ($this->_errors) {
            return false;
        }
        $attributes = array(
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        );
        if ($data['number']) {
            $attributes['creditCard'] = array(
                'number' => $data['number'],
                'expirationMonth' => $data['expirationMonth'],
                'expirationYear' => $data['expirationYear'],
                'cvv' => $data['cvv'],
                'cardholderName' => $data['cardholderName'],
                'billingAddress' => array(
                    'postalCode' => $data['postal_code'],
                    'streetAddress' => $data['streetAddress'],
                    'extendedAddress' => $data['extendedAddress'],
                    'locality' => $data['locality'],
                    'countryCodeAlpha2' => $data['countryCodeAlpha2'],
                )
            );
        }
        $this->_response = \Braintree_Customer::update($customerId, $attributes);
        return $this->_isValidResponse();
    }

    /**
     * @return \base\Application
     */
    private static function app()
    {
        if (null === self::$_app) {
            self::$_app = Registry::getApp();
        }
        return self::$_app;
    }

    public function generateToken()
    {
        $params = array();
        $customer = Customer::init()->getIdentity();
        if ($customer && $customer->braintree_id) {
            $params['customerId'] = $customer->braintree_id;
        }
        $token = \Braintree_ClientToken::generate($params);

        self::app()->getSession()->braintree()->token = $token;

        return $token;
    }

    public static function environment()
    {
        return self::app()->getConfig()->braintree->environment;
    }

    public static function merchantId()
    {
        return self::app()->getConfig()->braintree->merchantId;
    }

    public static function publicKey()
    {
        return self::app()->getConfig()->braintree->publicKey;
    }

    public static function privateKey()
    {
        return self::app()->getConfig()->braintree->privateKey;
    }

    public static function kountMerchantId()
    {
        return self::app()->getConfig()->braintree->kountMerchantId;
    }

    private function _validateCardData($cardData)
    {
        if (strlen(trim($cardData['cardholderName'])) == 0) {
            $this->_errors['cardholdername_error'] = \Error_strings::getError(PAYMENTPAGE_NAME_EMPTY);
        }
        if (strlen(trim($cardData['lastName'])) == 0) {
            $this->_errors['surname_error'] = \Error_strings::getError(PAYMENTPAGE_SURNAME_EMPTY);
        }
        if (strlen(trim($cardData['streetAddress'])) == 0) {
            $this->_errors['address_error'] = \Error_strings::getError(PAYMENTPAGE_ADDRESS_EMPTY);
        }
        if (strlen(trim($cardData['locality'])) == 0) {
            $this->_errors['city_error'] = \Error_strings::getError(PAYMENTPAGE_CITY_EMPTY);
        }
        if (strlen(trim($cardData['postalCode'])) == 0) {
            $this->_errors['postcode_error'] = \Error_strings::getError(PAYMENTPAGE_POSTCODE_EMPTY);
        }
        if (strlen(trim($cardData['countryCodeAlpha2'])) == 0) {
            $this->_errors['country_error'] = \Error_strings::getError(PAYMENTPAGE_COUNTRY_EMPTY);
        }
    }
    
    public function isValidCardType($cardType)
    {
        $bCard = $this->_response->customer->creditCards[0];
        $bCardType = strtoupper($bCard->cardType);
        $cardType = strtoupper($cardType);

        if ($cardType == 'UKE' || ($cardType == 'DELTA' && $bCardType == 'VISA' && $bCard->debit == 'Yes')) {
            $cardType = 'VISA';
        } elseif ($cardType == 'MC'  || ($cardType == 'MCDEBIT' && $bCardType == 'MASTERCARD' && $bCard->debit == 'Yes')) {
            $cardType = 'MASTERCARD';
        } elseif ($bCardType == 'AMERICAN EXPRESS' && $cardType == 'AMEX') {
            $cardType = 'AMERICAN EXPRESS';
        }

        return ($cardType == $bCardType);
    }
}
