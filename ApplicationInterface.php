<?php

namespace base;

interface ApplicationInterface {

    /**
     * Init the application
     * @param mixed $config array|ConfigInterface
     * @return self
     */
    public static function init($config);

    /**
     * Run the application
     *
     * @return self
     */
    public function run();

    /**
     * @return bool
     */
    public function isLocal();
    
    /**
     * Get the request object
     *
     * @return \base\helper\Request
     */
    public function getRequest();

    /**
     * Get the response object
     *
     * @return \base\helper\Response
     */
    public function getResponse();

}
