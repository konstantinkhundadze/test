<?php

namespace base\view;

class ViewBase extends ViewAbstract {

    protected $_path;
    protected $_layout = 'layout.php';
    protected $_template = 'error.php';

    protected $_content = '';

    protected  function _init()
    {
        parent::_init();
        self::$_layoutDisabled = !!self::app()->getRequest()->getIsAjax();
    }

    public function _renderContent()
    {
        extract($this->_data);
        ob_start();
        if (!$this->layoutDisabled()) {
            require ''.$this->getPath().$this->getTemplate();
        }
        $this->_content = ob_get_contents();
        ob_end_clean();
    }

    public function render()
    {
        $response = self::app()->getResponse();
        $this->_renderContent();
        if(!$response->isSent) {
            if (!$this->layoutDisabled()) {
                ob_start();
                require ''.$this->getPath().$this->getLayout();
                $response->content = ob_get_contents();
                ob_end_clean();
            } else {
                $response->content = $this->_content;
            }
            $response->send();
        }
        return $this;
    }

    /**
     * for old aplication
     * @todo
     */
    public function renderPartial()
    {
        extract($this->_data);
        ob_start();
        include($this->_template);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}
