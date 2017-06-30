<?php

namespace base\model;

use base\model\DBConnection;
use base\model\MySqlQueryBuilder;

use base\helper\Request;
use base\helper\String;

/**
 * @todo
 * @abstract
 */
class ModelPDO extends ModelBase {

    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var DBConnection
     */
    protected static $_db;

    protected static $_entityClass = '';

    public static $table = '';

    /**
     * $_POST
     * @var array 
     */
    protected static $_postData = array();

    public function __construct($connectionAlias = DBConnection::MCO_DB)
    {
        self::$_db = DBConnection::get($connectionAlias);
        self::$_postData = Request::post();
    }

    /**
     * @return \base\model\DBConnection
     */
    public function getdb()
    {
        return self::$_db;
    }

    /**
     * @return MySqlQueryBuilder
     */
    public function getBuilder()
    {
        return new MySqlQueryBuilder();
    }

    /**
     * @param type $table
     * @return MySqlQueryBuilder
     */
    public function select($table = '')
    {
        return $this->getBuilder()->select()->from($table ?: static::$table);
    }

    /**
     * @param mixed $id primaryKey column value
     * @return \base\model\entity\EntityBase OR false
     */
    public function find($id)
    {
        $entityClassName = $this->_getEntityClassName();
        $query = $this->select()->where($entityClassName::$primaryKey);
        $row = $this->findOne($query, $id);
        return $row ? $this->_toObject($row) : false;
    }

    /**
     * @param type $id primary key
     * @param type $table table name, default static::$table
     * @param type $pk primary key name, default Entity::$primaryKey
     * @return type
     */
    public function deleteOne($id, $table = '', $pk = '')
    {
        $table = $table ?: static::$table;
        $entityClassName = $this->_getEntityClassName();
        $pk = $pk ?: $entityClassName::$primaryKey;
        return self::$_db->execute('DELETE FROM `'.$table.'` WHERE `'.$pk.'`= ? ', $id);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function findOne($query, $params = array())
    {
        return self::$_db->fetchOne($query, $params);
    }
    
    /**
     * @param string $query
     * @param array $params
     * @param int $index column number
     * @return string single column
     */
    public function fetchColumn($query, $params = array(), $index = 0)
    {
        return self::$_db->fetchColumn($query, $params, $index);
    }

    /**
     * @param string $query
     * @param array $params
     * @return array
     */
    public function findRows($query, $params = array())
    {
        return self::$_db->fetchRows($query, $params);
    }

    /**
     * @param array $row
     * @return \base\model\entity\EntityBase
     */
    public function createRow($row)
    {
        return $this->_toObject($row)->save();
    }

    /**
     * @param array $data
     * @param string $table default ''
     * @return integer $_db->lastInsertId()
     */
    public function insert($data, $table = '', $ignore = false)
    {
        $table = $table ?: static::$table;
        $keys = implode('`,`', array_keys($data));
        $val = implode(',:', array_keys($data));
        $inputParams = array();
        foreach ($data as $k => $v) {
            $inputParams[":$k"] = $v;
        }
        $prefix = $ignore ? 'IGNORE' : '';
        $statement = self::$_db->prepare("INSERT $prefix INTO `$table` (`$keys`) VALUES (:$val)");
        $statement->execute($inputParams);
        return self::$_db->lastInsertId();
    }

    public function getTotalRows()
    {
        return $this->fetchColumn('SELECT FOUND_ROWS()');
    }

    /**
     * Initiates a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function beginTransaction()
    {
        return self::$_db->beginTransaction();
    }

    /**
     * Commits a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function commit()
    {
        return self::$_db->commit();
    }
  
    /**
     * Rolls back a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function rollBack()
    {
        return self::$_db->rollBack();
    }
    
    /**
     * 
     * @param array $data
     * @param string $condition
     * @param string $key
     * @param string $table
     * @return mixed $key OR false
     */
    public function update($data, $condition, $key, $table = '')
    {
        $table = $table ?: static::$table;
        $keys = '`' . implode('`=?,`', array_keys($data)) . '`=?';
        $inputParams = array();
        foreach ($data as $k => $v) {
            $inputParams[":$k"] = $v;
        }
        $statement = self::$_db->prepare("UPDATE `$table` SET $keys WHERE $condition");
        $inputParams = array_values($data);
        $inputParams[] = $key;
        return $statement->execute($inputParams) ? $key : false;
    }

    /**
     * @param array $row
     * @param $entity
     * @return \base\model\entity\EntityBase
     */
    protected function _toObject($row, $entity = null)
    {
        if (!$entity) {
            $entity = $this->_getEntityClass();
        }
        $entity->load($row);
        return $entity;
    }

    /**
     * @param array $rows
     * @param string $entity classname
     * @return array \base\model\entity\EntityBase Collection
     */
    protected function _toCollection($rows, $entity = '', $keysField = '')
    {
        $entity = $entity ?: $this->_getEntityClassName();
        $collection = array();
        foreach ($rows as $row) {
            if ($keysField) {
                $collection[$row[$keysField]] = $this->_toObject($row, new $entity)->load($row);
            } else {
                $collection[] = $this->_toObject($row, new $entity)->load($row);
            }
        }
        return $collection;
    }

    protected function _getEntityClass()
    {
        $className = $this->_getEntityClassName();
        $entity = new $className();
        return $entity;
    }

    protected function _getEntityClassName()
    {
        $className = static::$_entityClass;
        if (!$className) {
            $className = __NAMESPACE__ . '\\entity\\' . ucfirst(String::camelize(static::$table));
        }
        return $className;
    }

    /**
     * @param int $timestamp Unix timestamp default time()
     * @return type
     */
    public static function getDateTime($timestamp = '')
    {
        return date(ModelPDO::DATETIME_FORMAT, $timestamp ?: time());
    }

}
