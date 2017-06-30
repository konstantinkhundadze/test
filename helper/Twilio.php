<?php

namespace base\helper;

use base\helper\Registry;

require_once __DIR__ . '/../../twilio-php-master/Services/Twilio.php';

class Twilio extends \Services_Twilio {

    /**
     * @example Registry::getApp()->getConfig()->twilio
     * @var \base\helper\ConfigObject
     */
    private $_config;

    /**
     * @var string Exception::getMessage()
     */
    private $_error = '';

    public function __construct()
    {
        $this->_config = Registry::getApp()->getConfig()->twilio;
        parent::__construct($this->_config->sid, $this->_config->token, $this->_config->version);
    }

    public function getMessage()
    {
        return $this->_error;
    }

    /**
     * @param string $number Customer mobile number
     * @param string $message SMS body
     * @return mixed
     * @throws \Services_Twilio_RestException
     */
    public function sendMessage($number, $message)
    {
 
        $number = self::parsePhoneNumber($number);
        $message = self::parseSMSBody($message);
        try {
            return $this->account->messages->sendMessage($this->_config->sender, $number, $message);
        } catch (\Services_Twilio_RestException $ex) {
            $this->_error = $ex->getMessage();
            return false;
        }
    }

    public function createCall($number, $url, $options)
    {
        try {
            return $this->account->calls->create($this->getSender(), $number, $url, $options);
        } catch (\Services_Twilio_RestException $ex) {
            $this->_error = $ex->getMessage();
            return false;
        }
    }

    /**
     * @param mixed $number string|integer
     */
    public static function parsePhoneNumber($number)
    {
        $ex = explode('+', $number);
        if (!isset($ex[1])) {
        	
        	$number = $number/1;
        	
            if (strlen($number) == 10) {
                $number = '+44' . $number;
            } else {
                $number = '+' . $number;
            }
        }
        return $number;
    }

    /**
     * @param string $msg
     */
    public static function parseSMSBody($msg)
    {
        $msg = $msg . '';
        return $msg;
    }

    public function getSender()
    {
        return $this->_config->sender;
    }
}
