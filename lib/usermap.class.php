<?php

/********************************************************************

usermap.class.php released under MIT License
Manages user-generated maps on SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

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

$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once($config_dir.'/conf.db.php');
require_once('db.class.php');
require_once('user.class.php');
require_once('session.class.php');

class Usermap {

  private $id;
  private $uid;
  private $role;
  private $db;
  private $uid_q;

  function __construct($id) {
    session_start();
    if(!isset($_SESSION['simplemappr'])) {
      $this->access_denied();
    }
    Session::select_locale();
    $this->id = (int)$id;
    $this->uid = (int)$_SESSION['simplemappr']['uid'];
    $this->role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
    $this->uid_q = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : null;
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
    $this->db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    $this->restful_action();
  }

  /*
  * Detect type of request and perform appropriate method
  */
  private function restful_action() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
      case 'GET':
        if($this->id) {
          $this->show_map();
        } else {
          $this->index_maps();
        }
      break;

      case 'POST':
        $data = array(
          'uid' => $this->uid,
          'title' => $_POST['save']['title'],
          'map' => serialize($_POST),
          'created' => time(),
          'updated' => time()
        );

        //see if user's map by same title already exists
        $sql = "
          SELECT
            mid
          FROM
            maps
          WHERE
            uid=".$this->db->escape($this->uid)." AND title='".$this->db->escape($data['title'])."'";
        $record = $this->db->query_first($sql);

        if($record['mid']) {
          unset($data['created']);
          $this->db->query_update('maps', $data, 'mid='.$record['mid']);
          $mid = $record['mid'];
        } else {
          $mid = $this->db->query_insert('maps', $data);
        }

        header("Content-Type: application/json");
        echo '{"status":"ok", "mid":"'.$mid.'"}';
      break;

      case 'DELETE':
        $this->destroy_map();
      break;

      default:
      break;
    }
  }

  /*
  * Index method to produce table of maps
  */
  private function index_maps() {
    $where = array();
    $output = '';
    $data_uid = "";
    $dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
    $order = "m.created ".$dir;

    if(User::$roles[$this->role] !== 'administrator') {
      $where['user'] =  "WHERE m.uid = ".$this->db->escape($this->uid);
    }
    if($this->uid_q) {
      $where['user'] = "WHERE m.uid = ".$this->db->escape($this->uid_q);
    }

    $sql = "
      SELECT
        u.username AS username, COUNT(m.mid) AS total
      FROM
        maps m
      INNER JOIN
        users u ON (m.uid = u.uid)
      ".implode("",$where);

    $total = $this->db->query_first($sql);

    $b = "";
    if(isset($_GET['search'])) {
      if(User::$roles[$this->role] == 'administrator' && !$this->uid_q) { $b = "WHERE "; }
      $where['where'] = $b."LOWER(m.title) LIKE '%".$this->db->escape($_GET['search'])."%'";
      if(User::$roles[$this->role] == 'administrator' && !$this->uid_q) {
        $where['where'] .= " OR LOWER(u.username) LIKE '%".$this->db->escape($_GET['search'])."%'";
      }
    }
    if(isset($_GET['sort'])) {
      if($_GET['sort'] == "created" || $_GET['sort'] == "updated") {
        $order = "m.".$_GET['sort'] . " ".$dir;
      }
    }

    $sql = "
      SELECT
        m.mid,
        m.title,
        m.created,
        m.updated,
        u.uid,
        u.username 
      FROM 
        maps m 
      INNER JOIN
        users u ON (m.uid = u.uid)
      ".implode(" AND ", $where)."
      ORDER BY ".$order;

    $rows = $this->db->query($sql);

    if($total['total'] > 0) {
      $output .= '<table class="grid-usermaps">' . "\n";
      $output .= '<thead>' . "\n";
      $output .= '<tr>' . "\n";
      if($this->uid_q) {
        $header_count = sprintf(_("%d of %d for %s"), $this->db->affected_rows, $total['total'], $total['username']);
        $data_uid = " data-uid=".$this->uid_q;
      } else {
        $header_count = sprintf(_("%d of %d"), $this->db->affected_rows, $total['total']);
      }
      $output .= '<th class="left-align">'._("Title").' <input type="text" id="filter-mymaps" size="25" maxlength="35" value="" name="filter-mymap"'.$data_uid.' /> '.$header_count.'</th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "created" && isset($_GET['dir'])) ? " ".$dir : "";
      if(!isset($_GET['sort']) && !isset($_GET['dir'])) { $sort_dir = " desc"; }
      $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="created" href="#">'._("Created").'</a></th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "updated" && isset($_GET['dir'])) ? " ".$dir : "";
      $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="updated" href="#">'._("Updated").'</th>';
      $output .= '<th class="actions">'._("Actions");
      if(User::$roles[$this->role] == 'administrator') {
        $output .= '<a href="#" class="sprites-after toolsRefresh"></a>';
      }
      $output .= '</th>';
      $output .= '</tr>' . "\n";
      $output .= '</thead>' . "\n";
      $output .= '<tbody>' . "\n";
      $i=0;
      while ($record = $this->db->fetch_array($rows)) {
        $class = ($i % 2) ? 'class="even"' : 'class="odd"';
        $output .= '<tr '.$class.'>';
        $output .= '<td class="title">';
        $output .= (User::$roles[$this->role] == 'administrator' && !$this->uid_q) ? $record['username'] . ': ' : '';
        $output .= '<a class="map-load" data-id="'.$record['mid'].'" href="#">' . stripslashes($record['title']) . '</a>';
        $output .= '</td>';
        $output .= '<td class="center-align">' . gmdate("M d, Y", $record['created']) . '</td>';
        $output .= '<td class="center-align">';
        $output .= ($record['updated']) ? gmdate("M d, Y", $record['updated']) : ' - ';
        $output .= '</td>';
        $output .= '<td class="actions">';
        if($this->uid == $record['uid'] || User::$roles[$this->role] == 'administrator') {
          $output .= '<a class="sprites-before map-delete" data-id="'.$record['mid'].'" href="#">'._("Delete").'</a>';
        }
        $output .= '</td>';
        $output .= '</tr>' . "\n";
        $i++;
      }
      $output .= '</tbody>' . "\n";
      $output .= '</table>' . "\n";
    } else {
      $output .= '<div id="mymaps" class="panel ui-corner-all"><p>'._("Start by adding data on the Point Data or Regions tabs, press the Preview buttons there, then save your map from the top bar of the Preview tab.").'</p><p>'._("Alternatively, you may create and save a generic template by setting the extent, projection, and layer options you like without adding point data or specifying what political regions to shade.").'</p></div>';
    }

    header("Content-Type: text/html");
    echo $output;
  }

  /*
  * Show method to obtain map data
  */
  private function show_map() {
    $where = '';
    if(User::$roles[$this->role] !== 'administrator') { $where = ' AND uid = "'.$this->db->escape($this->uid).'"'; }
    $sql = '
      SELECT
        mid, map
      FROM 
        maps
      WHERE
        mid="'.$this->db->escape($this->id) . '"'.$where;
    $record = $this->db->query_first($sql);
    $data['mid'] = $record['mid'];
    $data['map'] = @unserialize($record['map']);
    $data['status'] = ($data['map']) ? 'ok' : 'failed';

    header("Content-Type: application/json");
    echo json_encode($data);
  }

  /*
  * Destroy method to delete a map
  */
  private function destroy_map() {
    $where = 'mid='.$this->db->escape($this->id);
    if(User::$roles[$this->role] !== 'administrator') {
      $where .= ' AND uid = '.$this->db->escape($this->uid);
    }
    $sql = '
      DELETE 
      FROM
        maps
      WHERE 
        '.$where;
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