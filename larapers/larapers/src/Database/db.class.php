<?php

/*
 * AUTHOR: Ivan Paul Bay
 * DATE: May 23, 2016
 * File: larapers/libs/db.class.php
 * Database utilities
 * Version 1.0
 */

namespace larapers\src\Database;


/**
 * Database helpers and query builder
 * @package larapers\libs
 * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
 * */
class db extends querybuilder {

    private $querystatement = array();
    protected static $sql = NULL;
    private static $joincounter = 0;
    private static $wherecounter = 0;
    private static $whereincounter = 0;
    private static $wherenullcounter = 0;
    private static $wherenotnullcounter = 0;
    private static $ordercounter = 0;

    /**
     * Specify the table to use in the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $tablename Table name
     * @return \self
     */
    public static function table($tablename = NULL) {
        self::$table = $tablename;
        self::$joincounter = 0;
        self::$wherecounter = 0;
        self::$whereincounter = 0;
        self::$ordercounter = 0;
        self::$wherenullcounter = 0;
        self::$wherenotnullcounter = 0;
        return new self;
    }

    /**
     * Create select statement that will use in the query
     * Specify the field(s) to select. By default, it will use * to select all
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $selectarr
     * @return \larapers\libs\db
     */
    public function select($selectarr) {
        if (is_array($selectarr) && !empty($selectarr)) {
            $this->querystatement['select'] = implode(", ", $selectarr);
        } else if (!is_array($selectarr) && $var != NULL && $selectarr != '') {
            $this->querystatement['select'] = $select;
        }
        return $this;
    }

    /**
     * Execute the select query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @return multidimensional array Result
     */
    public function get() {
        $this->querystatement['dml'] = "SELECT";
        $result = $this->prepquery($this->querystatement);

        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return $result;
        }
    }

    /**
     * Create insert statement
     * Indicates the field and value to be inserted
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param associative array $insertarr
     * @return Affected rows
     */
    public function insert($insertarr) {
        $this->querystatement['dml'] = "INSERT";
        $this->querystatement['insert'] = $insertarr;
        $result = $this->prepquery($this->querystatement);

        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return $result;
        }
    }

    /**
     * Create update statement
     * Indicates the field and value to be updated
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param associative array $updatearr
     * @return Affected rows
     */
    public function update($updatearr) {
        $this->querystatement['dml'] = "UPDATE";
        $this->querystatement['update'] = $updatearr;
        $result = $this->prepquery($this->querystatement);

        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return $result;
        }
    }

    /**
     * Execute the delete statement
     * 
     */
    public function delete() {
        $this->querystatement['dml'] = "DELETE";
        $result = $this->prepquery($this->querystatement);

        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return $result;
        }
    }

    /**
     * Create select first statement
     * Return specific number of records from the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param int $count Record count
     * @return \self
     */
    public function first($count = NULL) {
        $count = $count == NULL ? 1 : $count;
        $this->querystatement['dml'] = "SELECT";
        $this->querystatement['limit'] = "LIMIT " . $count;
        
        $result = $this->prepquery($this->querystatement);

        if (!empty($this->errors)) {
            return $this->errors;
        } else {
            return $result;
        }
    }

    /**
     * Create join statement
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $table Table name to join
     * @param string $one Main table field to compare with second table's field
     * @param string $operator Camparison operator
     * @param string $two Second table field to compare with main table's field
     * @return \larapers\libs\db
     */
    public function join($table, $one, $operator, $two) {
        $this->querystatement['join'][self::$joincounter]['type'] = 'INNER';
        $this->querystatement['join'][self::$joincounter]['table'] = $table;
        $this->querystatement['join'][self::$joincounter]['one'] = $one;
        $this->querystatement['join'][self::$joincounter]['operator'] = $operator;
        $this->querystatement['join'][self::$joincounter]['two'] = $two;
        self::$joincounter = self::$joincounter + 1;
        return $this;
    }

    /**
     * Creates where clause for the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $field
     * @param string $value
     * @param string $operator Def: "="
     * @return \larapers\libs\db
     */
    public function where($field, $value, $operator = "=") {
        $this->querystatement['where'][self::$wherecounter]['field'] = $field;
        $this->querystatement['where'][self::$wherecounter]['operator'] = $operator;
        $this->querystatement['where'][self::$wherecounter]['value'] = $value;
        self::$wherecounter = self::$wherecounter + 1;
        return $this;
    }

    /**
     * Creates "where in" statement and will append in where clause
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $field
     * @param string $values
     * @return \larapers\libs\db
     */
    public function whereIn($field, $values = array()) {
        $this->querystatement['wherein'][self::$whereincounter]['field'] = $field;
        $this->querystatement['wherein'][self::$whereincounter]['values'] = $values;
        self::$whereincounter = self::$whereincounter + 1;
        return $this;
    }

    /**
     * Creates "where null" statement and will append in where clause
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $field
     * @return \larapers\libs\db
     */
    public function whereNull($field) {
        $this->querystatement['wherenull'][self::$wherenullcounter] = $field;
        self::$wherenullcounter = self::$wherenullcounter + 1;
        return $this;
    }

    /**
     * Create "where not null" statement and will append in where clause
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $field
     * @return \larapers\libs\db
     */
    public function whereNotNull($field) {
        $this->querystatement['wherenotnull'][self::$wherenotnullcounter] = $field;
        self::$wherenotnullcounter = self::$wherenotnullcounter + 1;
        return $this;
    }

    /**
     * Creates "order by" statement and will append to the query
     * @param type $field
     * @param type $order
     * @return \larapers\libs\db
     */
    public function orderby($field, $order = 'asc') {
        if (is_array($field)) {
            $this->querystatement['orderby'] = $field;
        } else {
            $this->querystatement['orderby'][self::$ordercounter]['field'] = $field;
            $this->querystatement['orderby'][self::$ordercounter]['order'] = $order;
            self::$ordercounter = self::$ordercounter + 1;
        }
        return $this;
    }

    /**
     * Creates "group by" statement and will append to the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param string $field
     * @return \larapers\libs\db
     */
    public function groupby($field) {
        if (is_array($field)) {
            $this->querystatement['groupby'] = $field;
        } else {
            $this->querystatement['groupby'][] = $field;
        }
        return $this;
    }

    /**
     * Return generated query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @return Prepared statement
     */
    public static function getQueryLog() {
        return parent::$sql;
    }

    /**
     * Return variables used in the query
     * Sample return will be where clause used in the query 
     * @return array
     */
    public static function getvariables() {
        $self = new self;
        return $self->querystatement;
    }

}
