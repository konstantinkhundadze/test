<?php

namespace base\helper;

use base\Application;

class Registry extends RegistryAbstract {

    const KEY_APP = 'App';
    const KEY_REQUEST = 'Request';
    const KEY_RESPONSE = 'Response';

    const CUSTOMER_IDENTITY = 'CUSTOMER_IDENTITY';

    /**
     * for more convenience
     * @return \base\Application
     */
    static public function getApp()
    {
        $app = self::get(self::KEY_APP);
        if (null === $app) {
            throw new \Exception('The application is not initialized');
        }
        return $app;
    }

    /**
     * for more convenience
     * @return \base\helper\ConfigObject
     */
    static public function getConfig()
    {
        return self::getApp()->getConfig();
    }

    /**
     * for more convenience
     * @return \base\model\entity\GlobalSettings
     */
    static public function getSetting($name = '')
    {
        return self::getApp()->getSetting($name);
    }

    /**
     * for more convenience
     * @return \base\helper\Logger
     */
    static public function getLogger($alias = Application::APPLICATION_NAME)
    {
        return self::getApp()->getLogger($alias);
    }
}
