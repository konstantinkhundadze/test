<?php

namespace base\view;

use base\helper\Json;

/**
 * @todo
 */
class ViewJSON extends ViewAbstract {

    protected  function _init()
    {
        parent::_init();
        self::$_layoutDisabled = true;
        self::$_templateDisabled = true;
    }

    public function render()
    {
        $response = self::app()->getResponse();
        if(!$response->isSent) {
            $result = new \stdClass();
            $result->code = $response->getStatusCode();
            $result->status = $response->statusText;
            $result->data = $this->_data;

            $response->content = Json::encode($result);
            $response->send();
        }
        return $this;
    }

}
