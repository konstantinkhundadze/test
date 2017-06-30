<?php

namespace base\view;

use base\ApplicationInterface;

abstract class ViewAbstract implements ViewInterface {

    /**
     * @var \base\Application
     */
    protected static $_application;
    protected $_path;
    protected $_layout;
    protected $_template;
    protected static $_layoutDisabled = false;
    protected static $_templateDisabled = false;
    protected static $_helpersRegistry;

    /**
     * @var array data storage
     */
    protected $_data = array();
    public $appErrors = array();

    abstract public function render();

    public function __construct(ApplicationInterface $application)
    {
        static::$_application = $application;
        $this->_init();
    }

    protected function _init()
    {

    }

    public function getPath()
    {
        return $this->_path;
    }

    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    public function getLayout()
    {
        return $this->_layout;
    }

    public function setLayout($layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    public function getTemplate()
    {
        return $this->_template;
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * @return ApplicationInterface
     */
    protected function app()
    {
        return static::$_application;
    }

    public function helper()
    {
        if (null === self::$_helpersRegistry) {
            self::$_helpersRegistry = new HelpersRegistry();
        }
        return self::$_helpersRegistry;
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function set($name, $value)
    {
        $this->_data[$name] = $value;
        return $this;
    }

    public function get($name)
    {
        return $this->_data[$name];
    }

    public function layoutDisabled()
    {
        return static::$_layoutDisabled;
    }

    public function disableLayout()
    {
        static::$_layoutDisabled = true;
        return $this;
    }

    public function templateDisabled()
    {
        return static::$_templateDisabled;
    }

    public function disableTemplate()
    {
        static::$_templateDisabled = true;
        return $this;
    }

}
