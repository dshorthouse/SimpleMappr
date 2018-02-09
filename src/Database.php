<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
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
 */
namespace SimpleMappr;

use PDO;
use Symfony\Component\Yaml\Yaml;

/**
 * Database class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Database
{
    /** 
     * Database instance of self for static retrieval
     *
     * @var object $_instance
     */
    private static $_instance;

    /**
     * Database connection
     *
     * @var object $_link
     */
    private $_link;

    /**
     * Handle for prepared statements
     *
     * @var object $_handle
     */
    private $_handle;

    /**
     * Constructor
     */
    private function __construct()
    {
        $db_array = Yaml::parse(file_get_contents(ROOT . '/config/phinx.yml'));
        $config = [PDO::ATTR_PERSISTENT => true];
        $cred = $this->_credentials($db_array);
        $this->_link = new PDO($cred['conn'], $cred['user'], $cred['pass'], $config);
        $this->_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get the database instance
     *
     * @return object
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Prepare a SQL request
     *
     * @param string $sql A SQL statement.
     *
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
     *
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
     *
     * @return object The resultset
     */
    public function query($sql)
    {
        return $this->_link->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Bind parameters from a prepared SQL connection
     *
     * @param string $key   The parameter to be bound.
     * @param string $value The value to be set.
     * @param string $type  The type of data.
     *
     * @return void
     */
    public function bindParam($key, $value, $type = 'integer')
    {
        $pdo_type = ($type == 'integer') ? PDO::PARAM_INT : PDO::PARAM_STR;
        $this->_handle->bindParam($key, $value, $pdo_type);
    }

    /**
     * Execute a prepared handle
     *
     * @return void
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
    public function rowCount()
    {
        return $this->_handle->rowCount();
    }

    /**
     * Fetch the first record as an object.
     *
     * @return object The first resultset as an object.
     */
    public function fetchFirstObject()
    {
        $this->execute();
        return $this->_handle->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Fetch all records as an array of objects.
     *
     * @return array The resultset as an array of objects.
     */
    public function fetchAllObject()
    {
        $this->execute();
        return $this->_handle->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Fetch the first record as an array.
     *
     * @return array The first resultset as an array.
     */
    public function fetchFirstArray()
    {
        $this->execute();
        return $this->_handle->fetch();
    }

    /**
     * Fetch the all records as an array of arrays.
     *
     * @return array The resultset as an array of arrays.
     */
    public function fetchAllArray()
    {
        $this->execute();
        return $this->_handle->fetchAll();
    }

    /**
     * Insert a new record and return last inserted id.
     *
     * @param string $table The table name.
     * @param array  $data  An array of all data to be inserted.
     *
     * @return int The last inserted id.
     */
    public function queryInsert($table, $data = [])
    {
        if (empty($data)) {
            return;
        }

        $sql = "INSERT INTO {$table} ";
        $columns = array_keys($data);
        $sql .= "(" . implode(",", $columns) . ")";
        $sql .= " VALUES ";
        $sql .= "(" . implode(
            ",", array_map(
                function ($value) {
                    return ":{$value}";
                }, $columns
            )
        ) . ")";

        $this->prepare($sql);
        foreach ($data as $key => $value) {
            $this->bindParam(":{$key}", $value);
        }
        $this->execute();
        return $this->_lastInsert();
    }

    /**
     * Update an existing record.
     *
     * @param string $table The table name.
     * @param array  $data  An array of data to be updated.
     * @param string $where A where statement.
     *
     * @return int number of records affected
     */
    public function queryUpdate($table, $data, $where)
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

        $where_parts = [];
        if ($where) {
            $sql .= " WHERE ";
            $where_parts = explode("=", $where);
            $sql .= trim($where_parts[0]) . " = :{$where_parts[0]}";
        }

        $this->prepare($sql);
        foreach ($data as $key => $val) {
            $this->bindParam(":{$key}", $val);
        }
        if (count($where_parts) == 2) {
            $this->bindParam(":{$where_parts[0]}", trim($where_parts[1]));
        }
        $this->execute();
        return $this->rowCount();
    }

    /**
     * Destroy an existing record
     *
     * @param string  $table The table name.
     * @param integer $id    The id of the record.
     *
     * @return int number of records affected
     */
    public function queryDelete($table, $id)
    {
        if (!$id) {
            return;
        }

        $sql = "DELETE FROM {$table} WHERE id=:id";
        $this->prepare($sql);
        $this->bindParam(":id", $id, 'integer');
        $this->execute();
        return $this->rowCount();
    }

    /**
     * Make PDO connection string and get credentials from parsed Phinx YML.
     *
     * @param array $config Config array from parsed YML.
     *
     * @return array PDO connection, username, password.
     */
    private function _credentials($config)
    {
        $adapter = $config['environments'][ENVIRONMENT]['adapter'];
        $host = $config['environments'][ENVIRONMENT]['host'];
        $db = $config['environments'][ENVIRONMENT]['name'];
        $charset = $config['environments'][ENVIRONMENT]['charset'];
        $conn  = $adapter . ':host=' . $host . ';';
        $conn .= 'dbname=' . $db . ';';
        $conn .= 'charset=' . $charset;

        return [
            'conn' => $conn,
            'user' =>  $config['environments'][ENVIRONMENT]['user'],
            'pass' => $config['environments'][ENVIRONMENT]['pass']
        ];
    }

    /**
     * Return last inserted id.
     *
     * @return int The last inserted id.
     */
    private function _lastInsert()
    {
        return $this->_link->lastInsertId();
    }
}
