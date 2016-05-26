<?php

/*
 * AUTHOR: Ivan Paul Bay
 * DATE: May 23, 2016
 * File: larapers/libs/querybuilder.class.php
 * Database utilities
 * Version 1.0
 */

namespace larapers\src\Database;

use mysqli;
use larapers\src\Helpers\helpers;
use larapers\src\Helpers\response;

/**
 * Class that will help build query from raw statements
 * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
 */
class querybuilder {

    private static $_defaultdriver;
    private static $_hostname;
    private static $_db;
    private static $_user;
    private static $_pass;
    private $conn;
    protected $errors = array();
    protected static $table = null;
    private $raw_query;
    protected static $sql = null;

    /**
     * Create database connection
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @return void
     */
    private function connectDB() {

        self::$_defaultdriver = helpers::config_get("database.default");
        self::$_hostname = helpers::config_get("database.[connections|" . self::$_defaultdriver . "|host]");
        self::$_db = helpers::config_get("database.[connections|" . self::$_defaultdriver . "|database]");
        self::$_user = helpers::config_get("database.[connections|" . self::$_defaultdriver . "|username]");
        self::$_pass = helpers::config_get("database.[connections|" . self::$_defaultdriver . "|password]");

        $this->conn = new mysqli(self::$_hostname, self::$_user, self::$_pass, self::$_db);
        if ($this->conn->connect_error) {
            die('Connect Error (' . $this->conn->connect_errno . ') ' . $this->conn->connect_error);
            return "Unable to connect to the database " . $this->conn->connect_error;
        }
    }

    /**
     * Prepare the query
     * Combine all where statements, select statements, insert statements and all other statements that were generated
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @return array Result
     */
    protected function prepquery($raw_query) {
        #var_dump($raw_query);
        $this->raw_query = $raw_query;

        $sqlraw = array();
        $iswhere = false;

        if (empty(self::$table)) {
            return $this->errors['errors'][] = "No table selected";
        }

        switch ($this->raw_query['dml']) {
            case "SELECT":
                $select = '';
                
                if (isset($this->raw_query['select'])) {
                    $select .= " " . $this->raw_query['select'] . " ";
                } else {
                    $select .= ' * ';
                }
                
                $sqlraw['select'] = $select;

                break;

            case "INSERT":
                $insert = '';
                $insert .= " (" . implode(", ", array_keys($this->raw_query['insert'])) . ")";
                $insert .= " VALUES ";
                $bindparams = array();
                for ($a = 0; $a < count($this->raw_query['insert']); $a++) {
                    $bindparams[] = "?";
                }
                $insert .= "(" . implode(", ", $bindparams) . ")";
                $sqlraw['insert'] = $insert;
                break;

            case "UPDATE":
                $update = '';

                if (isset($this->raw_query['update'])) {
                    if (is_array($this->raw_query['update'])) {
                        $update .= " SET ";
                        foreach ($this->raw_query['update'] as $field => $value) {
                            $update .= $field . " = ?, ";
                        }
                    }
                }
                $sqlraw['update'] = rtrim($update, ", ");
                break;

            default:
                break;
        }

        if (isset($this->raw_query['join']) && !empty($this->raw_query['join'])) {
            $join = '';
            for ($a = 0; $a < count($this->raw_query['join']); $a++) {
                $type = $this->raw_query['join'][$a]['type'];
                $table = $this->raw_query['join'][$a]['table'];
                $one = $this->raw_query['join'][$a]['one'];
                $operator = $this->raw_query['join'][$a]['operator'];
                $two = $this->raw_query['join'][$a]['two'];

                $join .= " " . $type . " JOIN " . $table;
                $join .= " ON " . $one . " " . $operator . " " . $two;
            }
            $sqlraw['join'] = $join;
        }

        if (isset($this->raw_query['where']) && !empty($this->raw_query['where'])) {
            $where = '';
            for ($a = 0; $a < count($this->raw_query['where']); $a++) {
                $prefix = $a == 0 ? 'WHERE' : 'AND';
                $field = $this->raw_query['where'][$a]['field'];
                $operator = $this->raw_query['where'][$a]['operator'];
                $value = $this->raw_query['where'][$a]['value'];

                $where .= " " . $prefix . " " . $field . " " . $operator . " ?";
            }
            $sqlraw['where'] = $where;
            $iswhere = true;
        }

        if (isset($this->raw_query['wherein']) && !empty($this->raw_query['wherein'])) {
            $where = '';
            for ($a = 0; $a < count($this->raw_query['wherein']); $a++) {
                $prefix = $iswhere == true ? 'AND' : 'WHERE';
                $field = $this->raw_query['wherein'][$a]['field'];
                $value = $this->raw_query['wherein'][$a]['values'];
                $bindparams = array();
                for ($a = 0; $a < count($value); $a++) {
                    $bindparams[] = "?";
                }

                $where .= " " . $prefix . " " . $field . " IN (" . implode(", ", $bindparams) . ")";
            }
            $sqlraw['where'] = $iswhere == true ? $sqlraw['where'] . $where : $where;
            $iswhere = true;
        }

        if (isset($this->raw_query['wherenull']) && !empty($this->raw_query['wherenull'])) {
            $where = '';
            for ($a = 0; $a < count($this->raw_query['wherenull']); $a++) {
                $prefix = $iswhere == true ? 'AND' : 'WHERE';
                $field = $this->raw_query['wherenull'][$a];
                $where .= " " . $prefix . " " . $field . " IS NULL";
            }

            $sqlraw['where'] = $iswhere == true ? $sqlraw['where'] . $where : $where;
            $iswhere = true;
        }

        if (isset($this->raw_query['wherenotnull']) && !empty($this->raw_query['wherenotnull'])) {
            $where = '';
            for ($a = 0; $a < count($this->raw_query['wherenotnull']); $a++) {
                $prefix = $iswhere == true ? 'AND' : 'WHERE';
                $field = $this->raw_query['wherenotnull'][$a];
                $where .= " " . $prefix . " " . $field . " IS NOT NULL";
            }

            $sqlraw['where'] = $iswhere == true ? $sqlraw['where'] . $where : $where;
            $iswhere = true;
        }

        if (isset($this->raw_query['groupby']) && !empty($this->raw_query['groupby'])) {
            $groupby = '';
            for ($a = 0; $a < count($this->raw_query['groupby']); $a++) {
                $prefix = $a == 0 ? 'GROUP BY' : ',';
                $field = $this->raw_query['groupby'][$a];

                $groupby .= " " . $prefix . " " . $field;
            }
            $sqlraw['groupby'] = $groupby;
        }

        if (isset($this->raw_query['orderby']) && !empty($this->raw_query['orderby'])) {
            $orderby = '';
            if (!helpers::is_arr_key_int($this->raw_query['orderby'])) {
                $a = 0;
                foreach ($this->raw_query['orderby'] as $field => $order) {
                    $prefix = $a == 0 ? 'ORDER BY' : ',';
                    $orderby .= " " . $prefix . " " . $field . " " . $order;
                    $a++;
                }
            } else {
                for ($a = 0; $a < count($this->raw_query['orderby']); $a++) {
                    $prefix = $a == 0 ? 'ORDER BY' : ',';
                    $field = $this->raw_query['orderby'][$a]['field'];
                    $order = $this->raw_query['orderby'][$a]['order'];

                    $orderby .= " " . $prefix . " " . $field . " " . $order;
                }
            }
            $sqlraw['orderby'] = $orderby;
        }

        $this->sqlwrapper($sqlraw);
        return $this->execquery();
    }

    /**
     * Prepare and build the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param array $sqlraw Raw parts of the query to build
     * @return \larapers\libs\db
     */
    private function sqlwrapper($sqlraw) {

        switch ($this->raw_query['dml']) {
            case "SELECT":
                self::$sql = $this->raw_query['dml'];
                self::$sql .= $sqlraw['select'];
                self::$sql .= " FROM ";
                self::$sql .= self::$table;
                self::$sql .= isset($sqlraw['join']) ? $sqlraw['join'] : '';
                self::$sql .= isset($sqlraw['where']) ? $sqlraw['where'] : '';
                self::$sql .= isset($sqlraw['groupby']) ? $sqlraw['groupby'] : '';
                self::$sql .= isset($sqlraw['orderby']) ? $sqlraw['orderby'] : '';
                
                if (isset($this->raw_query['limit'])) {
                    self::$sql .= " " . $this->raw_query['limit'] . " ";
                }
                break;

            case "UPDATE":
                self::$sql = $this->raw_query['dml'] . " ";
                self::$sql .= self::$table;
                self::$sql .= isset($sqlraw['join']) ? $sqlraw['join'] : '';
                self::$sql .= $sqlraw['update'];
                self::$sql .= isset($sqlraw['where']) ? $sqlraw['where'] : '';
                self::$sql .= isset($sqlraw['groupby']) ? $sqlraw['groupby'] : '';
                self::$sql .= isset($sqlraw['orderby']) ? $sqlraw['orderby'] : '';
                break;

            case "INSERT":
                self::$sql = $this->raw_query['dml'] . " INTO ";
                self::$sql .= self::$table;
                self::$sql .= $sqlraw['insert'];
                break;

            case "DELETE":
                $this->sql = $this->raw_query['dml'];
                self::$sql .= " FROM ";
                self::$sql .= self::$table;
                self::$sql .= $sqlraw['where'];
                break;
        }

        return $this;
    }

    /**
     * Execute built query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @return Result
     */
    private function execquery() {

        $this->connectDB();
        $sql = self::$sql;
        $stmt = $this->conn->prepare($sql);

        if (false === $stmt) {
            return $this->errors['errors'][] = htmlspecialchars($this->conn->error);
        }

        $paramlist = array();

        /* create binding for INSERT */
        if (isset($this->raw_query['insert'])) {
            $paramlist = array_values($this->raw_query['insert']);
        }

        /* create binding for UPDATE */
        if (isset($this->raw_query['update'])) {
            foreach ($this->raw_query['update'] as $field => $value) {
                $paramlist[] = $value;
            }
        }

        /* create binding for WHERE clause */
        if (isset($this->raw_query['where'])) {
            foreach ($this->raw_query['where'] as $key => $value) {
                $paramlist[] = $value['value'];
            }
        }

        /* create binding for WHERE IN clause */
        if (isset($this->raw_query['wherein'])) {
            foreach ($this->raw_query['wherein'] as $key => $value) {
                foreach ($value['values'] as $key2 => $value2) {
                    $paramlist[] = $value2;
                }
            }
        }

        $binding = $this->prepbindvariables($stmt, $paramlist);

        if (false === $binding) {
            return $this->errors['errors'][] = htmlspecialchars($stmt->error);
        }

        $execution = $stmt->execute();

        if (false === $execution) {
            return $this->errors['errors'][] = htmlspecialchars($stmt->error);
        }

        switch ($this->raw_query['dml']) {
            case "SELECT":
                $result = $stmt->get_result();

                $return = array();

                while ($data = $result->fetch_assoc()) {
                    $return[] = $data;
                }
                break;

            case "UPDATE":
            case "INSERT":
            case "DELETE":
                $return = $stmt->affected_rows;
                break;

            default:
                break;
        }

        $this->dbclose();
        return response::object($return);
    }

    /**
     * Prepare the bind parameters that will be use in the query
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     * @param Prepared statement $stmt
     * @param array $params
     * @return Prepared statement
     */
    private function prepbindvariables($stmt, $params) {

        if (empty($params)) {
            return $stmt;
        }

        if ($params != null) {
            // Generate the Type String (eg: 'issisd')
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    // Integer
                    $types .= 'i';
                } elseif (is_float($param)) {
                    // Double
                    $types .= 'd';
                } elseif (is_string($param)) {
                    // String
                    $types .= 's';
                } else {
                    // Blob and Unknown
                    $types .= 'b';
                }
            }

            // Add the Type String as the first Parameter
            $bind_names[] = $types;

            // Loop thru the given Parameters
            for ($i = 0; $i < count($params); $i++) {
                // Create a variable Name
                $bind_name = 'bind' . $i;
                // Add the Parameter to the variable Variable
                $$bind_name = $params[$i];
                // Associate the Variable as an Element in the Array
                $bind_names[] = &$$bind_name;
            }

            // Call the Function bind_param with dynamic Parameters
            call_user_func_array(array($stmt, 'bind_param'), $bind_names);
        }
        return $stmt;
    }

    /**
     * Close database connection
     * @author Ivan Paul Bay <ivan.bay@maximintegrated.com>
     */
    private function dbclose() {
        $this->conn = null;
    }

}
