<?php

namespace base\model\entity;

use base\helper\String;

class EntityBase implements EntityInterface {

    public static $primaryKey = 'id';
    
    protected $_readOnly = false;

    /**
     * @var array
     */
    protected static $_auxiliaryFields = array();

    /**
     * @var string
     */
    protected static $_modelName;

    /**
     * @var type \base\model\ModelPDO
     */
    private $_model;

    public function __construct() {}

    public static function getClassName() {
        return get_called_class();
    }

    public function __get($name)
    {
        throw new \Exception('Unknown column - '.$name);
    }

    /**
     * @return \base\model\ModelPDO
     */
    protected function _getModel()
    {
        if (null === $this->_model) {
            if (!$modelClassName = static::$_modelName) {
                $modelClassName = str_replace('\entity', '', get_class($this));
            }
            $this->_model = new $modelClassName();
        }
        return $this->_model;
    }

    /**
     * @return prymary key value
     */
    public function getPK()
    {
        return $this->{static::$primaryKey};
    }

    public function load($data)
    {
        $this->_beforeLoad();

        $vars = call_user_func('get_object_vars', $this);

        foreach ($vars as $k => $v) {
            if (isset($data[$k]) && $this->validate($k, $v)) {
                $this->{$k} = $data[$k];
            }
        }

        $this->_afterLoad();

        return $this;
    }

    public function save()
    {
        $this->_beforeSave();

        if (!$this->validate()) {
            return $this;
        }

        if (get_class($this) == __CLASS__ || $this->_readOnly) {
            throw new \Exception('this entity is read only');
        }

        $data = $this->toArray();

        if (static::$_auxiliaryFields) {
            foreach (static::$_auxiliaryFields as $af) {
                unset($data[$af]);
            }
        }

        $model = $this->_getModel();
        if ($this->getPK()) {
            $model->update($data, static::$primaryKey . '=?', $this->getPK());
        } else {
            $this->{static::$primaryKey} = $model->insert($data);
        }

        $this->_afterSave();

        return $this;
    }

    public function decorate($decoratorName = '')
    {
        $nameSpace = __NAMESPACE__ . '\\decorator\\';
        if (!$decoratorName) {
            $ex = explode('\\', $this->getClassName());
            $decoratorName = end($ex);
        }
        $decorator = $nameSpace . $decoratorName;
        return new $decorator($this);
    }

    public function toArray()
    {
        return call_user_func('get_object_vars', $this);
    }

    /**
     * @todo
     */
    public function validate()
    {
        return $this;
    }

    protected function _beforeLoad()
    {

    }

    protected function _afterLoad()
    {

    }

    protected function _beforeSave()
    {

    }

    protected function _afterSave()
    {

    }

}
