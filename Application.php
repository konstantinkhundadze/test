<?php

namespace base;

use base\model\DBConnection;
use base\model\ModelBase;
use base\model\GlobalSettings;

use base\helper\Request;
use base\helper\Response;
use base\helper\Registry;
use base\helper\Logger;
use base\helper\SessionManager;

use base\exception\InvalidConfigException;
use Symfony\Component\ClassLoader\UniversalClassLoader as AutoLoader;

class Application implements ApplicationInterface {

    const APPLICATION_NAME = 'COGOTRIP';

    const ADMIN_NAMESPACE = 'admin';
    const INTERNAL_NAMESPACE = 'internal';
    const INTERNAL2_NAMESPACE = 'internal2';
    const PUBLIC_NAMESPACE = 'www';
    const DEFAULT_CONTROLLER = 'index';
    const DEFAULT_ACTION = 'index';

    /**
     * @deprecated
     */
    public static $db;

    /**
     * @var base\model\DBConnection store
     */
    private static $_dbConnections;

    /**
     * @var \base\helper\Logger Colection
     */
    private static $_logger = array();

    /**
     * @var \base\helper\Config
     */
    private static $_config;

    /**
     * @var \base\view\ViewBase
     */
    private static $_view;

    /**
     * @var \base\helper\Request
     */
    private static $_request = null;

    /**
     * @var \base\helper\Response
     */
    private static $_response;

    /**
     * @var \base\helper\SessionManager
     */
    private static $_sessionManager;

    private static $_instanceName = self::PUBLIC_NAMESPACE;
    private static $_controllerName = self::DEFAULT_CONTROLLER;
    private static $_actionName = self::DEFAULT_ACTION;

    /**
     * @var \base\controller\ControllerBase
     */
    private static $_controller;

    /**
     * Init the Application
     * @param \base\helper\Config $config
     * @param string $instanceName
     * @return Application new self()
     */
    public static function init($config)
    {
        try {
            $instance = Registry::getApp();
        } catch (\Exception $e) {
            $instance = new self();

            $instance::$_config = $config;

            $instance::_initMvc();
            $instance->_initRoute();
            $instance->_initView();

            Registry::set(Registry::KEY_APP, $instance);
            Registry::lock(Registry::KEY_APP);

            if ($instance->getRequest()->getUserIP()) {
                $instance::$db = $instance->getDB(ModelBase::MCO_DB);
                self::$db->query("INSERT IGNORE INTO uniquehits SET ip = '{$instance->getRequest()->getUserIP()}', `date` = NOW()");
            }
        }
        return $instance;
    }

    public function getDB()
    {
        if (null == self::$db) {
            self::$db = ModelBase::getConnection();
    }
        return self::$db;
    }

    /**
     * @param type $connectionAlias
     * @return DBConnection
     */
    public function getConnection($connectionAlias = DBConnection::MCO_DB)
    {
        if (!isset(self::$_dbConnections[$connectionAlias])) {
            self::$_dbConnections[$connectionAlias] = DBConnection::get($connectionAlias);
        }
        return self::$_dbConnections[$connectionAlias];
    }

    /**
     * @return \base\helper\Config::isLocal()
     */
    public function isLocal()
    {
        return self::$_config->isLocal();
    }

    /**
     * @return \base\helper\ConfigObject
     * @throws InvalidConfigException
     */
    public function getConfig()
    {
        return self::$_config;
    }

    /**
     * @param string $name
     * @return \base\model\entity\GlobalSettings
     */
    public function getSetting($name = '')
    {
        $settingsModel = new GlobalSettings();
        return $settingsModel->getSetting($name);
    }

    private static function _initMvc()
    {
        $instances = array();
        foreach (self::getInstancesNames() as $instance => $params) {
            $instances[$instance] = __DIR__ . DIRECTORY_SEPARATOR . '..';
        }

        $classLoader = new AutoLoader();
        $classLoader->registerNamespaces($instances);
        $classLoader->register();
    }

    private function _initRoute()
    {
        $route = $this->_parseRoute($this->getRequest()->getPathInfo());
        self::$_controllerName = $route->controller;
        self::$_actionName = $route->action;
    }

    /**
     * @param string $route
     * @return \stdClass
     */
    private function _parseRoute($route)
    {
        $parsed = new \stdClass();
        $parsed->controller = self::DEFAULT_CONTROLLER;
        $parsed->action = self::DEFAULT_ACTION;
        $exPI = explode('/', $route);
        if (!empty($exPI[0])) {
            $parsed->controller = $exPI[0];
            if (!empty($exPI[1])) {
                $parsed->action = $exPI[1];
            }
        }
        return $parsed;
    }

    protected function _initView()
    {
        if ($this->getRequest()->getIsJson()) {
            self::$_view = new view\ViewJSON($this);
        } else {
            self::$_view = new view\ViewBase($this);
        }
    }

    public static function getInstancesNames()
    {
        return array(
            self::ADMIN_NAMESPACE => array(),
            self::INTERNAL_NAMESPACE => array(),
            self::INTERNAL2_NAMESPACE => array(),
            self::PUBLIC_NAMESPACE => array(),
        );
    }

    public function getInstanceName()
    {
        return self::$_instanceName;
    }

    public function getControllerName()
    {
        return self::$_controllerName;
    }

    public function getActionName()
    {
        return self::$_actionName;
    }

    /**
     *
     * @return view\ViewBase
     */
    public function getView()
    {
        if (null == self::$_view) {
            self::_initView();
        }
        return self::$_view;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (null === self::$_request) {
            $request = Request::init();
            $pathInfo = explode('/', $request->getPathInfo());
            $requestParams = array();
            if (!empty($pathInfo[2])) {
                unset($pathInfo[0]);
                unset($pathInfo[1]);
                foreach ($pathInfo as $k => $v) {
                    if ($k % 2 == 0) {
                        $requestParams[$v] = $pathInfo[$k + 1];
                    }
                }
            }
            self::$_request = $request->setQueryParams($requestParams);
        }
        return self::$_request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null == self::$_response) {
            self::$_response = Response::init();
        }
        return self::$_response;
    }

    /**
     * @return SessionManager
     */
    public function getSession()
    {
        if (null == self::$_sessionManager) {
            self::$_sessionManager = SessionManager::init();
        }
        return self::$_sessionManager;
    }

    /**
     * @param string $alias
     * @return Logger
     */
    public function getLogger($alias = self::APPLICATION_NAME)
    {
        if (empty(self::$_logger[$alias])) {
            self::$_logger[$alias] = Logger::init($alias);
        }
        return self::$_logger[$alias];
    }

    /**
     * @param string $instanceName default Application::PUBLIC_NAMESPACE
     * @return boolean
     */
    public function isControllerAction($instanceName = self::PUBLIC_NAMESPACE, $route = '')
    {
        if ($route) {
            $parsed = $this->_parseRoute($route);
            $controller = $parsed->controller;
            $action = $parsed->action;
        } else {
            $controller = self::$_controllerName;
            $action = self::$_actionName;
        }
        $controllerName = '\\' . $instanceName . '\\mvc\\controller\\' . ucfirst($controller);

        return (class_exists($controllerName) && method_exists($controllerName, $controllerName::normalizeActionName($action)));
    }

    public function run($instanceName = self::PUBLIC_NAMESPACE, $routePath = '')
    {
        self::$_instanceName = $instanceName;
        if ($routePath) {
            $route = $this->_parseRoute($routePath);
            self::$_controllerName = $route->controller;
            self::$_actionName = $route->action;
        }

        self::$_view->setPath(__DIR__ . '/../' . self::$_instanceName . '/mvc/view/');

        $controllerName = '\\' . self::$_instanceName . '\\mvc\\controller\\' . ucfirst(self::$_controllerName);
        self::$_controller = class_exists($controllerName) ? new $controllerName($this) : new \base\controller\ControllerBase($this);

        if (!self::$_view->templateDisabled()) {
            self::$_view->setTemplate(self::$_controllerName . '/' . self::$_actionName . '.php');
        }

        set_exception_handler(array($this, 'hendlerException'));
//        set_error_handler(array($this, 'hendlerError'));

        self::$_controller->setAction(self::$_actionName);

        return self::$_controller->run();
    }

    public function hendlerException($exception)
    {
        restore_error_handler();
        restore_exception_handler();

        if ($exception instanceof \base\exception\HttpExceptionInterface) {
            $this->getResponse()->setStatusCode($exception->getCode());
        }
        self::$_actionName = 'error';

        if (!self::$_view->templateDisabled()) {
            self::$_view->setTemplate(self::$_actionName . '.php');
        }
        self::$_view->appErrors[] = $exception;

        return self::$_controller->setAction(self::$_actionName)->run();
    }

    public function hendlerError($code = 0, $message = '', $file = '', $line = '', $params = array())
    {
        ini_set('display_errors', false);

        self::$_actionName = 'error';

        if (!self::$_view->templateDisabled()) {
            self::$_view->setTemplate(self::$_actionName . '.php');
        }

        self::$_view->appErrors[] = new helper\ErrorObject($code, $message, $file, $line, $params);
        return false;
    }

}