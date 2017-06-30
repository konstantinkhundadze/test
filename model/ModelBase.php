<?php

namespace base\model;

use base\helper\Registry;
use base\helper\Request;
use base\model\DBConnection;

/**
 * @todo
 */
include_once __DIR__ . '/../../generic_classes/database.php';
include_once __DIR__ . '/../../generic_classes/application.php';

/**
 * @deprecated
 * @use ModelPDO
 */
class ModelBase {

    /**
     * @deprecated
     * @use \base\model\DBConnection::MCO_DB
     */
    const MCO_DB = 'Mco';

    /**
     * @access private
     * @var \Database
     */
    protected static $_dbConnection;

    /**
     * Application object.
     * @access protected
     * @var \base\Application
     */
    protected $_app;

    /**
     * Errors store
     * @access private
     * @var mixed
     */
    protected $_errorMessage = array();

    /**
     * $_POST
     * @var array 
     */
    protected static $_postData = array();

    public function __construct()
    {
        $this->_app = Registry::getApp();
        self::$_postData = Request::post();
    }

    public function __destruct()
    {
        if (is_resource(self::$_dbConnection)) {
            mysql_free_result(self::$_dbConnection);
        }
        self::$_dbConnection = null;
    }

    /**
     * @deprecated
     * @return \Database
     */
    protected static function _getDB($dbName = DBConnection::MCO_DB)
    {
        if (!is_resource(self::$_dbConnection)) {
            $dbName = ($dbName == DBConnection::MCO_DB) ? 'Mco' : $dbName;
            self::$_dbConnection = new \Database(true, $dbName);
        }
        return self::$_dbConnection;
    }

    /**
     * @depreceted
     */
    public static function getConnection($dbName = DBConnection::MCO_DB)
    {
        return self::_getDB($dbName);
    }

    /**
     * This function parse error id and get error message.
     * @param integer &$error_id Error Id
     */
    public function parse_error(&$error_id)
    {
        $err_text = array(' error ' . $error_id);
        array_push($this->_errorMessage, $err_text);
    }

    /**
     * Function returns error message array.
     * @return mixed Array of error messages
     */
    public function get_errors()
    {
        return $this->_errorMessage;
    }

    /**
     * Function is used to log end of the function.
     * @param string $class_name  Name of the class
     * @param string &$function_name Name of the function
     */
    public function log_start($class_name, $function_name)
    {
        $this->_app->getLogger()->debug('Start of function ' . $class_name . '::' . $function_name);
    }

    /**
     * Function is used to log end of the function.
     * @param string $class_name  Name of the class
     * @param string &$function_name Name of the function
     */
    public function log_end($class_name, $function_name)
    {
        $this->_app->getLogger()->debug('End of function ' . $class_name . '::' . $function_name);
    }

    /**
     * Function is used to log any other debug message in controller.
     * @param string $class_name  Name of the class
     * @param string &$function_name Name of the function
     * @param string &$query Query string to log
     */
    public function log_query($class_name, $function_name, $query)
    {
        $this->_app->getLogger()->debug('' . $class_name . '::' . $function_name . '--' . $query);
    }

}
