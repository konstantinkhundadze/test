<?php

namespace base\model;

use \PDO;
use base\helper\Registry;

class DBConnection {

    const MCO_DB = 'mco_db';
    const PAF_DB = 'paf_db';
    const CSV_DB = 'csv_db';

    /**
     * @deprecated
     */
    public $error;

    /**
     * @var PDO 
     */
    private $_pdo;
    protected $hasActiveTransaction = false;

    /**
     * @see self::get()
     * @param type $dsn
     * @param type $user
     * @param type $pass
     * @param type $connectionAlias default MCO_DB
     */
    public function __construct($connectionAlias = self::MCO_DB)
    {
        $config = Registry::getApp()->getConfig()->$connectionAlias;

        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        $this->_pdo = @new PDO(
            "$config->engine:host=$config->host;dbname=$config->dbname",
            $config->user,
            $config->password,
            $options
        );

        Registry::set($connectionAlias, $this);
        Registry::lock($connectionAlias);
    }

    public function __destruct()
    {
        $this->_pdo = null;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_pdo, $name), $arguments);
    }

    /**
     * Return BD conection from Registry or create new one
     * @param type $connectionAlias default 'mco_db'
     * @return DBConnection
     */
    public static function get($connectionAlias = self::MCO_DB)
    {
        if (!Registry::get($connectionAlias)) {
            try {
                $instance = new self($connectionAlias);
            } catch (\PDOException $e) {
                die('Connection failed: ' . $e->getMessage());
            }
            return $instance;
        }
        return Registry::get($connectionAlias);
    }

    /**
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    private function _executeQuery($query, $params = array())
    {
        $sth = $this->_pdo->prepare($query);
        $sth->execute((array) $params);
        return $sth;
    }

    /**
     * @param string $query
     * @param array $params
     * @return type
     */
    public function execute($query, $params = array())
    {
        return $this->_executeQuery($query, $params);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchOne($query, $params = array())
    {
        return $this->_executeQuery($query, $params)->fetch() ? : array();
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchRows($query, $params = array())
    {
        return $this->_executeQuery($query, $params)->fetchAll() ? : array();
    }

    /**
     * @param string $query
     * @param array $params
     * @param int $index column number
     * @return string <b>\PDOStatement::fetchColumn</b> returns a single column
     */
    public function fetchColumn($query, $params = array(), $index = 0)
    {
        return $this->_executeQuery($query, $params)->fetchColumn($index);
    }

    public function prepare($statement, $driver_options = array())
    {
        return $this->_pdo->prepare($statement, $driver_options);
    }

    /**
     * Initiates a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function beginTransaction()
    {
        if ($this->hasActiveTransaction) {
            throw new \PDOException('Transaction was started');
        } else {
            $this->hasActiveTransaction = $this->_pdo->beginTransaction();
            return $this->hasActiveTransaction;
        }
    }

    /**
     * Commits a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function commit()
    {
        if ($done = $this->_pdo->commit()) {
            $this->hasActiveTransaction = false;
        }
        return $done;
    }

    /**
     * Rolls back a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function rollBack()
    {
        if ($done = $this->_pdo->rollBack()) {
            $this->hasActiveTransaction = false;
        }
        return $done;
    }

}
