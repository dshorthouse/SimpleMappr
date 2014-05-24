<?php

/********************************************************************

Database.class.php released under MIT License
Manages database connections & queries for SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2014 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

namespace SimpleMappr;

class Database {

  private $link;
  private $handle;

  function __construct() {
    $this->link = new \PDO(DB_DSN, DB_USER, DB_PASS);
    $this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->link->setAttribute(\PDO::ATTR_PERSISTENT, false);
  }

  public function prepare($sql) {
    $this->handle = $this->link->prepare($sql);
    return $this->handle;
  }

  public function exec($sql) {
    return $this->link->exec($sql);
  }

  public function query($sql) {
    return $this->link->query($sql)->fetchAll(\PDO::FETCH_OBJ);
  }

  public function bind_param($key, $value, $type = 'integer') {
    $pdo_type = ($type == 'integer') ? \PDO::PARAM_INT : \PDO::PARAM_STR;
    $this->handle->bindParam($key, $value, $pdo_type);
  }

  public function execute() {
    $this->handle->execute();
  }

  public function row_count() {
    return $this->handle->rowCount();
  }

  public function fetch_first_object() {
    $this->execute();
    return $this->handle->fetch(\PDO::FETCH_OBJ);
  }

  public function fetch_all_object() {
    $this->execute();
    return $this->handle->fetchAll(\PDO::FETCH_OBJ);
  }

  public function fetch_first_array() {
    $this->execute();
    return $this->handle->fetch();
  }

  public function fetch_all_array() {
    $this->execute();
    return $this->handle->fetchAll();
  }

  public function query_insert($table, $data = array()) {
    if(empty($data)) { return; }

    $sql = "INSERT INTO {$table} ";
    $columns = array_keys($data);
    $sql .= "(" . implode(",",$columns) . ")";
    $sql .= " VALUES ";
    $sql .= "(" . implode(",", array_map(function($value) { return ":{$value}"; }, $columns)) . ")"; 

    $this->prepare($sql);
    foreach($data as $key => $value) {
      $this->bind_param(":{$key}", $value);
    }
    $this->execute();
    return $this->last_insert();
  }

  public function query_update($table, $data = array(), $where) {
    if(empty($data)) { return; }

    $sql = "UPDATE {$table} SET ";
    $updates = "";
    foreach($data as $key => $val) {
      $updates .= "$key = :{$key}, ";
    }
    $sql .= rtrim($updates, ', ');

    $where_parts = array();
    if($where) {
      $sql .= " WHERE ";
      $where_parts = explode("=", $where);
      $sql .= trim($where_parts[0]) . " = :{$where_parts[0]}";
    }

    $this->prepare($sql);
    foreach($data as $key => $val) {
      $this->bind_param(":{$key}", $val);
    }
    if(count($where_parts) == 2) {
      $this->bind_param(":{$where_parts[0]}", trim($where_parts[1]));
    }
    $this->execute();
  }

  private function last_insert() {
    return $this->link->lastInsertId();
  }
}