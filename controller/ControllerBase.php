<?php

namespace base\controller;

use base\view\ViewInterface;
use base\exception\HttpException;

class ControllerBase extends ControllerAbstract {

    public function __construct(\base\ApplicationInterface $application)
    {
        static::$_application = $application;
        static::$_request = $application->getRequest();
        static::$_response = $application->getResponse();

        $this->_setView(static::$_application->getView());
    }

    public function setAction($actionName)
    {
        static::$_action = static::normalizeActionName($actionName);
        if (!method_exists($this, static::$_action)) {
            throw new HttpException('The requested URL was not found on this server.', 404);
        }
        return $this;
    }

    public function run()
    {
        $actionName = static::$_action;
        $this->_beforeAction();
        $this->$actionName();
        $this->_afterAction();
    }

    protected function _beforeAction()
    {
        $headers = self::$_response->getHeaders();
        $headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $headers->set('Last-Modified', 'Mon, 26 Jul 1997 05:10:00 GMT');
        $headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
        $headers->set('Pragma', 'no-cache');
    }

    /**
     * @return \base\Application
     */
    protected function app()
    {
        return static::$_application;
    }

    protected function _afterAction()
    {
        $view = $this->_getView();
        foreach ($this->_data as $key => $value) {
            $view->$key = $value;
        }
        $view->render();
    }

    /**
     * @return \base\view\ViewBase
     */
    protected function _getView()
    {
        if (null === static::$_view) {
            static::$_view = static::$_application->getView();
        }
        return static::$_view;
    }

    protected function _setView(ViewInterface $view)
    {
        static::$_view = $view;
    }

    public static function normalizeActionName($actionName)
    {
        return 'action' . ucfirst($actionName);
    }

    public function redirect($url, $statusCode = 302)
    {
        return $this->app()->getResponse()->redirect($url, $statusCode);
    }

    public function goHome()
    {
        return $this->redirect('/');
    }

    public function actionError()
    {

    }
}
