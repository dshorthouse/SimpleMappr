<?php
namespace SimpleMappr;

/**
 * Database.class.php released under MIT License
 * Manages database connections & queries for SimpleMappr
 *
 * PHP Version >= 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse {{{
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * }}}
 */
class Database
{
    private $_link;
    private $_handle;

    function __construct()
    {
        $this->_link = new \PDO(DB_DSN, DB_USER, DB_PASS);
        $this->_link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_link->setAttribute(\PDO::ATTR_PERSISTENT, false);
    }

    /**
     * Prepare a SQL request
     *
     * @param string $sql A SQL statement.
     * @return object The connection handle.
     */
    public function prepare($sql)
    {
        $this->_handle = $this->_link->prepare($sql);
        return $this->_handle;
    }

    /**
     * Execute a SQL request
     *
     * @param string $sql A SQL statement
     * @return object The resultset
     */
    public function exec($sql)
    {
        return $this->_link->exec($sql);
    }

    /**
     * Query a SQL request
     *
     * @param string $sql A SQL statement
     * @return object The resultset
     */
    public function query($sql)
    {
        return $this->_link->query($sql)->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Bind parameters from a prepared SQL connection
     *
     * @param string $key The parameter to be bound.
     * @param string/int $value The value to be set.
     * @param string/int $type The type of data.
     * @return object The connection handle.
     */
    public function bind_param($key, $value, $type = 'integer')
    {
        $pdo_type = ($type == 'integer') ? \PDO::PARAM_INT : \PDO::PARAM_STR;
        $this->_handle->bindParam($key, $value, $pdo_type);
    }

    /**
     * Execute a prepared handle
     */
    public function execute()
    {
        $this->_handle->execute();
    }

    /**
     * Obtain a row count from executed handle.
     *
     * @return int The affected row count of the last executed statement.
     */
    public function row_count()
    {
        return $this->_handle->rowCount();
    }

    /**
     * Fetch the first record as an object.
     *
     * @return object The first resultset as an object.
     */
    public function fetch_first_object()
    {
        $this->execute();
        return $this->_handle->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * Fetch all records as an array of objects.
     *
     * @return array The resultset as an array of objects.
     */
    public function fetch_all_object()
    {
        $this->execute();
        return $this->_handle->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Fetch the first record as an array.
     *
     * @return array The first resultset as an array.
     */
    public function fetch_first_array()
    {
        $this->execute();
        return $this->_handle->fetch();
    }

    /**
     * Fetch the all records as an array of arrays.
     *
     * @return array The resultset as an array of arrays.
     */
    public function fetch_all_array()
    {
        $this->execute();
        return $this->_handle->fetchAll();
    }

    /**
     * Insert a new record and return last inserted id.
     *
     * @param string $table The table name.
     * @param array $data An array of all data to be inserted.
     * @return int The last inserted id.
     */
    public function query_insert($table, $data = array())
    {
        if (empty($data)) {
            return;
        }

        $sql = "INSERT INTO {$table} ";
        $columns = array_keys($data);
        $sql .= "(" . implode(",", $columns) . ")";
        $sql .= " VALUES ";
        $sql .= "(" . implode(",", array_map(function ($value) { return ":{$value}"; }, $columns)) . ")"; 

        $this->prepare($sql);
        foreach ($data as $key => $value) {
            $this->bind_param(":{$key}", $value);
        }
        $this->execute();
        return $this->last_insert();
    }

    /**
     * Update an existing record.
     *
     * @param string $table The table name.
     * @param array $data An array of data to be updated.
     * @param string $where A where statement.
     */
    public function query_update($table, $data = array(), $where)
    {
        if (empty($data) || !$where) {
            return;
        }

        $sql = "UPDATE {$table} SET ";
        $updates = "";
        foreach ($data as $key => $val) {
            $updates .= "$key = :{$key}, ";
        }
        $sql .= rtrim($updates, ', ');

        $where_parts = array();
        if ($where) {
            $sql .= " WHERE ";
            $where_parts = explode("=", $where);
            $sql .= trim($where_parts[0]) . " = :{$where_parts[0]}";
        }

        $this->prepare($sql);
        foreach ($data as $key => $val) {
            $this->bind_param(":{$key}", $val);
        }
        if (count($where_parts) == 2) {
            $this->bind_param(":{$where_parts[0]}", trim($where_parts[1]));
        }
        $this->execute();
    }

    /**
     * Return last inserted id,
     *
     * @return int The last inserted id.
     */
    private function last_insert()
    {
        return $this->_link->lastInsertId();
    }
}