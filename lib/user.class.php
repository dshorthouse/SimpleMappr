<?php

/**************************************************************************

File: users.class.php

Description: Produces a list of users

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once($config_dir.'conf.db.php');
require_once('db.class.php');
require_once('session.class.php');

class User {

  private $id;
  private $uid;
  private $db;

  function __construct($id) {
    session_start();
	if(!isset($_SESSION['simplemappr'])) {
	  $this->access_denied();
	}
    Session::select_locale();
    $this->id = (int)$id;
    $this->uid = (int)$_SESSION['simplemappr']['uid'];
    $this->set_header()->execute();
  }

  /*
  * Set header to prevent caching
  */
  private function set_header() {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    return $this;
  }

  /*
  * Utility method
  */
  private function execute() {
    if($this->uid !== 1) {
      $this->access_denied();
    } else {
      $this->db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $this->restful_action();
    }
  }

  /*
  * Detect type of request and perform appropriate method
  */
  private function restful_action() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
      case 'GET':
        $this->index_users();
      break;

      case 'POST':
      break;

      case 'DELETE':
        $this->destroy_user();
      break;

      default:
      break;
    }
  }

  /*
  * Index method to produce table of users
  */
  private function index_users() {
    $dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
    $order = "u.access ".$dir;

    if(isset($_GET['sort'])) {
      $order = "";
      if($_GET['sort'] == "num" || $_GET['sort'] == "access" || $_GET['sort'] == "username") {
        if($_GET['sort'] == "accessed") { $order = "m."; }
        if($_GET['sort'] == "username") { $order = "u."; }
        $order = $order.$this->db->escape($_GET['sort'])." ".$dir;
      }
    }

    $sql = "
      SELECT
        u.uid, u.username, u.email, u.access, count(m.mid) as num
      FROM
        users u
      LEFT JOIN
        maps m ON (u.uid = m.uid)
      GROUP BY
        u.username
      ORDER BY ".$order;

   $rows = $this->db->query($sql);

   $output = "";

   if($this->db->affected_rows > 0) {
     $output .= '<table class="grid-users">' . "\n";
     $output .= '<thead>' . "\n";
     $output .= '<tr>' . "\n";
     $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "username" && isset($_GET['dir'])) ? " ".$dir : "";
     $output .= '<th class="left-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="username" href="#">'._("Username").'</a></th>';
     $output .= '<th class="left-align">'._("Email").'</th>';
     $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "num" && isset($_GET['dir'])) ? " ".$dir : "";
     $output .= '<th><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="num" href="#">'._("Maps").'</a></th>';
     $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "access" && isset($_GET['dir'])) ? " ".$dir : "";
     if(!isset($_GET['sort']) && !isset($_GET['dir'])) { $sort_dir = " desc"; }
     $output .= '<th><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="access" href="#">'._("Last Access").'</a></th>';
     $output .= '<th class="actions">'._("Actions").'<a href="#" class="sprites-after toolsRefresh"></a></th>';
     $output .= '</tr>' . "\n";
     $output .= '</thead>' . "\n";
     $output .= '<tbody>' . "\n";
     $i=0;
     while ($record = $this->db->fetch_array($rows)) {
       $class = ($i % 2) ? 'class="even"' : 'class="odd"';
       $output .= '<tr '.$class.'>';
       $output .= '<td><a class="user-load" data-uid="'.$record['uid'].'" href="#">';
       $output .= stripslashes($record['username']);
       $output .= '</a></td>';
       $output .= '<td>'.stripslashes($record['email']).'</td>';
       $output .= '<td class="usermaps-number">'.$record['num'].'</td>';
       $access = ($record['access']) ? gmdate("M d, Y", $record['access']) : '-';
       $output .= '<td class="usermaps-center">'.$access.'</td>';
       $output .= '<td class="actions">';
       if($record['uid'] != 1) {
         $output .= '<a class="sprites-before user-delete" data-id="'.$record['uid'].'" href="#">'._("Delete").'</a>';
       }
       $output .= '</td>';
       $output .= '</tr>' . "\n";
       $i++;
     }
     $output .= '</tbody>' . "\n";
     $output .= '</table>' . "\n";
   }

   header("Content-Type: text/html");
   echo $output;
  }

  /*
  * Destroy method to delete a user
  */
  private function destroy_user() {
    $sql = "
        DELETE
          u, m
        FROM
          users u
        LEFT JOIN
          maps m ON u.uid = m.uid
        WHERE 
          u.uid=".$this->db->escape($this->id);
    $this->db->query($sql);

    header("Content-Type: application/json");
    echo '{"status":"ok"}';
  }

  private function access_denied() {
	header("Content-Type: application/json");
    echo '{ "error" : "access denied" }';
    exit();
  }

}

?>