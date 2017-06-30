<?php

namespace base\model;

use base\exception\InvalidParamException;

/**
 * Class MySqlBuilder
 * @package base\model
 */
class MySqlQueryBuilder {

    const SQL_WILDCARD = '*';
    const SQL_AS = 'AS';
    const SQL_SELECT = 'SELECT';
    const SQL_INSERT = 'INSERT';
    const SQL_UPDATE = 'UPDATE';
    const SQL_DELETE = 'DELETE';
    const SQL_FROM = 'FROM';
    const SQL_JOIN = 'JOIN';
    const SQL_INNER = 'INNER';
    const SQL_LEFT = 'LEFT';
    const SQL_RIGHT = 'RIGHT';
    const SQL_UNION = 'UNION';
    const SQL_WHERE = 'WHERE';
    const SQL_AND = 'AND';
    const SQL_OR = 'OR';
    const SQL_ON = 'ON';
    const SQL_IN = 'IN';
    const SQL_HAVING = 'HAVING';
    const SQL_ORDER_BY = 'ORDER BY';
    const SQL_GROUP_BY = 'GROUP BY';
    const SQL_ASC = 'ASC';
    const SQL_DESC = 'DESC';
    const SQL_LIMIT = 'LIMIT';

    private $_locket = false;
    private $_sql = '';
    private $_columns = self::SQL_WILDCARD;
    private $_from = '';
    private $_where = '';
    private $_join = '';
    private $_order = '';
    private $_group = '';
    private $_limit = '';

    private function _isLocket()
    {
        if ($this->_locket) {
            throw new InvalidParamException('Builder is locket');
        }
        $this->_locket = true;
    }

    /**
     * @param array $columns
     */
    public function columns($columns)
    {
        if (is_array($columns)) {
            foreach ($columns as $k => $column) {
                $alias = '';
                if (!is_numeric($k)) {
                    $alias = ' ' . self::SQL_AS . ' ' . $k;
                }
                if (strpos($column, '(') === false && strpos($column, '.') === false && strpos($column, '`') === false) {
                    $column = '`' . $column . '`';
                }
                $arr[] = $column . $alias;
            }
            $this->_columns = implode(', ', $arr);
        } elseif (!empty($columns)) {
            $colArr = explode(',', $columns);
            $this->columns($colArr);
        }
        return $this;
    }

    /**
     * @todo
     * @param string or array $from
     * @return MySqlQueryBuilder
     */
    public function from($table)
    {
        if ($table && is_array($table)) {
            $tables = array();
            foreach ($table as $k => $v) {
                if (is_numeric($k)) {
                    $tables[] = '`' . $v . '`';
                } else {
                    $tables[] = '`' . $v . '` AS ' . $k;
                }
            }
            $from = implode(',', $tables);
        } else {
            $from = '`'.$table.'`';
        }
        $this->_from = ($this->_from ? ',' : ' ' . self::SQL_FROM).' '.$from;

        return $this;
    }

    /**
     * @param mixed $field string OR array <b>(alias => field)</b>
     * @param string $holder
     * @param string $operator example: >,=,<,>=...
     * @return MySqlQueryBuilder
     */
    public function where($field, $holder = '?', $operator = '=')
    {
        $this->_where($field, $operator, $holder, self::SQL_AND);
        return $this;
    }

    /**
     * @param string , array $field
     * @param string $holder
     * @param string $operator example: >,=,<,>=...
     * @return MySqlQueryBuilder
     */
    public function orWhere($field, $holder = '?', $operator = '=')
    {
        $this->_where($field, $operator, $holder, self::SQL_OR);
        return $this;
    }

    private function _where($field, $operator, $holder, $where)
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                if (is_numeric($k)) {
                    $k = $v;
                }
                $holder = $v;

                $ex = explode(' ', trim($k));
                if (!empty($ex[1]) && in_array(trim($ex[1]), array('>', '<', '=', '<>', '><', '>=', '<=', 'LIKE'))) {
                    $operator = trim($ex[1]);
                    $k = $ex[0];
                }

                $keyArr = explode('.', trim($k));
                $fieldName = '`' . implode('`.`', $keyArr) . '`';

                $this->_where($fieldName, $operator, $holder, $where);
            }
        } else {
            $postfix = '';
            if (strtoupper(trim($operator)) == self::SQL_IN) {
                $operator = $operator . '(';
                $postfix = ')';
            }
            $this->_where .= ((!$this->_where) ? ' ' . self::SQL_WHERE : $where) . ' ' ;
            $holder = $holder ? ($holder == '?') ? '?' : ':' . $holder : '';
            $this->_where .= $field . ' ' . $operator . ' ' . $holder . ' ' . $postfix;
        }
    }

    /**
     * @param string $table
     * @param string $cond
     * @param string $type
     * @return MySqlQueryBuilder
     */
    public function join($table, $cond, $type = self::SQL_INNER)
    {
        $type = strtoupper($type);
        $types = array(self::SQL_INNER, self::SQL_LEFT, self::SQL_RIGHT, self::SQL_UNION);
        if (!in_array($type, $types)) {
            throw new InvalidParamException("Invalid JOIN type '" . $type . "'");
        }
        if (is_array($table)) {
            if (count($table) != 1) {
                throw new InvalidParamException('table in ' . $type . 'JOIN must be one');
            }
            $alias = key($table);
            $name = current($table);
            $table = (is_numeric($alias)) ? '`' . $name . '`' : '`' . $name . '` ' . self::SQL_AS . ' ' . $alias;
        }
        $this->_join .= ' ' . $type . ' ' . self::SQL_JOIN . ' ' . $table . ' ' . self::SQL_ON . ' ' . $cond;
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return MySqlQueryBuilder
     */
    public function limit($limit, $offset = 0)
    {
        $limit = (int)$limit;
        if ($offset) {
            $offset = (int)$offset;
        }
        $this->_limit .= ' ' . self::SQL_LIMIT . ' ' . $offset . ', ' . $limit;
        return $this;
    }

    /**
     * @param string $field
     * @param string $type
     * @return MySqlQueryBuilder
     */
    public function order($field, $type = self::SQL_ASC)
    {
        $type = strtoupper($type);
        $types = array(self::SQL_ASC, self::SQL_DESC);
        if (!in_array($type, $types)) {
            throw new InvalidParamException("Invalid ORDER BY type '" . $type . "'");
        }
        $this->_order = (!$this->_order) ? ' ' . self::SQL_ORDER_BY . ' ' : ', ';
        $this->_order .= $field . ' ' . $type;
        return $this;
    }

    /**
     * @param $field
     * @return MySqlQueryBuilder
     */
    public function group($field)
    {
        $this->_group .= ' ' . self::SQL_GROUP_BY . ' ' . $field;
        return $this;
    }

    /**
     * @param $field
     * @return MySqlQueryBuilder
     */
    public function calculateRows()
    {
        $this->_sql .= 'SQL_CALC_FOUND_ROWS ';
        return $this;
    }

    /**
     * @return MySqlQueryBuilder
     */
    public function select($type = '')
    {
        $this->_isLocket();
        $this->_sql = self::SQL_SELECT . ' ' . $type . ' ';
        return $this;
    }

    public function insert()
    {
        $this->_isLocket();
        $this->_sql = self::SQL_INSERT . ' ';
        return $this;
    }

    public function update()
    {
        $this->_isLocket();
        $this->_sql = self::SQL_UPDATE . ' ';
        return $this;
    }

    public function delete()
    {
        $this->_isLocket();
        $this->_sql = self::SQL_DELETE . ' ';
        return $this;
    }

    /**
     * @todo
     * @return string
     */
    private function _render()
    {
        return $this->_sql
                . $this->_columns
                . $this->_from
                . $this->_join
                . $this->_where
                . $this->_group
                . $this->_order
                . $this->_limit;
    }

    public function __toString()
    {
        return $this->_render();
    }

}
