<?php

namespace base\controller;

use base\view\ViewInterface;

abstract class ControllerAbstract {

    /**
     * @var \base\Application
     */
    protected static $_application;

    /**
     * @var \base\helper\Request
     */
    protected static $_request;

    /**
     * @var \base\helper\Response
     */
    protected static $_response;

    /**
     * @var \base\view\ViewBase
     */
    protected static $_view;
    
    protected static $_action;
    
    protected $_data = array();

    abstract protected function __construct(\base\ApplicationInterface $application);

    abstract protected function _getView();

    abstract protected function _setView(ViewInterface $view);
}
