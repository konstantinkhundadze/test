<?php

namespace base\helper;

class ErrorObject {

    public $code = 0;
    public $message = '';
    public $file = '';
    public $line = '';
    public $params = array();
    
    public function __construct($code = 0, $message = '', $file = '', $line = '', $params = array())
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        $this->params = $params;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function getMessage()
    {
        return $this->message;
    }

    public function getTraceAsString()
    {
        return "$this->file ($this->line) [" . implode(' | ', $this->params) . ']';
    }
}
