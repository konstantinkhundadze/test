<?php

namespace base\helper;

use base\helper\Registry;

require_once __DIR__ . '/../../data/log4php/Logger.php';

/**
 * @see https://minicabit.atlassian.net/browse/RT-875
 * @see https://logging.apache.org/log4php/docs/configuration.html
 * 
 * @example $logger = \base\helper\Registry::getLogger($alias);
 */
class Logger extends \Logger {

    public static function init($name)
    {
        $conf = Registry::getConfig()->log4php;
        Logger::configure(array(
            'rootLogger' => array(
                'appenders' => array('default'),
            ),
            'appenders' => array(
                'default' => array(
                    'class' => $conf->default['class'],
                    'layout' => array(
                        'class' => $conf->default['layout']
                    ),
                    'params' => array(
                        'file' => $conf->default['file'],
                        'append' => true
                    )
                )
            )
        ));

        return self::getLogger($name);
    }

}
