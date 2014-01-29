<?php
# Name: Database.class.php
# File Description: MySQL Class to allow easy and clean access to common mysql commands
# Author: ricocheting
# Web: http://www.ricocheting.com/
# Update: 2009-08-25
# Version: 2.2
# Copyright 2003 ricocheting.com


/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/



//require("config.inc.php");
//$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);


###################################################################################################
###################################################################################################
###################################################################################################
class Database {


  private $server   = ""; //database server
  private $user     = ""; //database login name
  private $pass     = ""; //database login password
  private $database = ""; //database name
  private $pre      = ""; //table prefix


  #######################
  private $mysqli = "";
  private $query_obj = "";

  //number of rows affected by SQL query
  public $affected_rows = 0;


  #-#############################################
  # desc: constructor
  function __construct($server, $user, $pass, $database, $pre=''){
    $this->server = $server;
    $this->user = $user;
    $this->pass = $pass;
    $this->database = $database;
    $this->pre = $pre;

    $this->connect();
  }#-#constructor()

  function __destruct() {
    $this->close();
  }

  #-#############################################
  # desc: connect and select database using vars above
  function connect() {
    $this->mysqli = new mysqli($this->server, $this->user, $this->pass, $this->database);

    if ($this->mysqli->connect_errno) {//open failed
      $this->oops("Could not connect to server: <b>$this->server</b>.");
    }
    
    if(!$this->mysqli->ping()) {
      $this->oops("Error");
    }

    $this->query("SET NAMES 'utf8'");

    // unset the data so it can't be dumped
    $this->server = '';
    $this->user = '';
    $this->pass = '';
    $this->database = '';
  }#-#connect()


  #-#############################################
  # desc: close the connection
  function close() {
    $thread = $this->mysqli->thread_id;
    $this->mysqli->kill($thread);
    if(!$this->mysqli->close()) {
      $this->oops("<b>Could not close connection</b>");
    }
  }#-#close()


  #-#############################################
  # Desc: escapes characters to be mysql ready
  # Param: string
  # returns: string
  function escape($string) {
    if(get_magic_quotes_runtime()) { $string = stripslashes($string); }
    return $this->mysqli->real_escape_string($string);
  }#-#escape()


#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc
function query($sql) {
    // do query
    $this->query_obj = $this->mysqli->query($sql);

    if (!$this->query_obj) {
        $this->oops("<b>MySQL Query fail:</b> $sql");
        return 0;
    }
    
    $this->affected_rows = $this->mysqli->affected_rows;

    return $this->query_obj;
}#-#query()


#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)
function fetch_array($query_obj = -1) {
    // retrieve row
    if (is_object($query_obj)) {
      $this->query_obj = $query_obj;
    }

    if (isset($this->query_obj)) {
      $record = $this->query_obj->fetch_assoc();
    }else{
      $this->oops("Invalid query: ".serialize($this->query_obj)." Records could not be fetched.");
    }

    return $record;
}#-#fetch_array()


#-#############################################
# desc: returns all the results (not one row)
# param: (MySQL query) the query to run on server
# returns: assoc array of ALL fetched results
function fetch_all_array($sql) {
    $query_obj = $this->query($sql);
    $out = array();

    while ($row = $this->fetch_array($query_obj)){
        $out[] = $row;
    }

    $this->free_result($query_obj);
    return $out;
}#-#fetch_all_array()


#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used
function free_result($query_obj = -1) {
    if (is_object($query_obj)) {
        $this->query_obj = $query_obj;
    }
    $this->query_obj->free();
    if($this->mysqli->error) {
        $this->oops("Result could not be freed.");
    }
}#-#free_result()


#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results
function query_first($query_string) {
    $query_obj = $this->query($query_string);
    $out = $this->fetch_array($query_obj);
    $this->free_result($query_obj);
    return $out;
}#-#query_first()


#-#############################################
# desc: does an update query with an array
# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
# returns: (query_id) for fetching results etc
function query_update($table, $data, $where='1') {
    $q="UPDATE `".$this->pre.$table."` SET ";

    foreach($data as $key => $val) {
        if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
        elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
        else $q.= "`$key`='".$this->escape($val)."', ";
    }

    $q = rtrim($q, ', ') . ' WHERE '.$where.';';

    return $this->query($q);
}#-#query_update()


#-#############################################
# desc: does an insert query with an array
# param: table (no prefix), assoc array with data
# returns: id of inserted record, false if error
function query_insert($table, $data) {
    $q="INSERT INTO `".$this->pre.$table."` ";
    $v=''; $n='';

    foreach($data as $key=>$val) {
        $n.="`$key`, ";
        if(strtolower($val)=='null') $v.="NULL, ";
        elseif(strtolower($val)=='now()') $v.="NOW(), ";
        else $v.= "'".$this->escape($val)."', ";
    }

    $q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

    if($this->query($q)){
        return $this->mysqli->insert_id;
    }
    else return false;

}#-#query_insert()


#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display
function oops($msg = '') {
    ?>
        <table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
        <tr><th colspan=2>Database Error</th></tr>
        <tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
        <?php if(strlen($this->mysqli->error)>0) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>'.$this->mysqli->error.'</td></tr>'; ?>
        <tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
        <tr><td align="right">Script:</td><td><a href="<?php echo @$_SERVER['REQUEST_URI']; ?>"><?php echo @$_SERVER['REQUEST_URI']; ?></a></td></tr>
        <?php if(strlen(@$_SERVER['HTTP_REFERER'])>0) echo '<tr><td align="right">Referer:</td><td><a href="'.@$_SERVER['HTTP_REFERER'].'">'.@$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
        </table>
    <?php
}#-#oops()


}//CLASS Database
###################################################################################################

?>