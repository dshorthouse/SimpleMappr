<?php

/********************************************************************

Usermap.class.php released under MIT License
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

namespace SimpleMappr;

class Usermap extends Rest implements RestMethods {

  private $uid;
  private $role;
  private $db;
  private $uid_q;
  private $total;
  private $dir;

  function __construct($id) {
    session_start();
    if(!isset($_SESSION['simplemappr'])) {
      Utilities::access_denied();
    }
    Session::select_locale();
    $this->id = (int)$id;
    $this->uid = (int)$_SESSION['simplemappr']['uid'];
    $this->role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
    $this->uid_q = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : null;
    Header::set_header();
    $this->execute();
  }

  /*
  * Utility method
  */
  private function execute() {
    $this->db = new Database();
    $this->restful_action();
  }

  /*
  * Implemented index method
  */
  public function index() {

    $sql = "
      SELECT
        u.username, COUNT(m.mid) AS total
      FROM
        maps m
      INNER JOIN
        users u ON (m.uid = u.uid)";
    $where = array();
    if(User::$roles[$this->role] !== 'administrator') {
      $sql .=  " WHERE m.uid = :uid";
      $where['user'] = " WHERE m.uid = :uid";
      $this->db->prepare($sql);
      $this->db->bind_param(":uid", $this->uid);
    } else {
      if($this->uid_q) {
        $sql .= " WHERE m.uid = :uid_q";
        $where['user'] = " WHERE m.uid = :uid_q";
        $this->db->prepare($sql);
        $this->db->bind_param(":uid_q", $this->uid_q);        
      } else {
        $this->db->prepare($sql);
      }
    }

    $this->total = $this->db->fetch_first_object();

    $this->dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
    $order = "m.created {$this->dir}";
    
    $b = "";
    if(isset($_GET['search'])) {
      if(User::$roles[$this->role] == 'administrator' && !$this->uid_q) { $b = " WHERE "; }
      $where['where'] = $b."LOWER(m.title) LIKE :search";
      if(User::$roles[$this->role] == 'administrator' && !$this->uid_q) {
        $where['where'] .= " OR LOWER(u.username) LIKE :search";
      }
    }
    if(isset($_GET['sort'])) {
      if($_GET['sort'] == "created" || $_GET['sort'] == "updated") {
        $order = "m.".$_GET['sort'] . " {$this->dir}";
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

    $this->db->prepare($sql);
    if(User::$roles[$this->role] !== 'administrator') {
      $this->db->bind_param(":uid", $this->uid, 'integer');
    } else {
      if($this->uid_q) {
        $this->db->bind_param(":uid_q", $this->uid_q, 'integer');
      }
    }
    if(isset($_GET['search'])) {
      $this->db->bind_param(":search", "%{$_GET['search']}%", 'string');
    }
    $this->produce_output($this->db->fetch_all_object());
  }

  /*
  * Implemented show method
  */
  public function show($id) {
    $sql = "
      SELECT
        mid, map
      FROM 
        maps
      WHERE
        mid = :mid";

    if(User::$roles[$this->role] == 'administrator') {
      $this->db->prepare($sql);
      $this->db->bind_param(":mid", $id, 'integer');
    } else {
      $sql .= " AND uid = :uid";
      $this->db->prepare($sql);
      $this->db->bind_param(":mid", $id, 'integer');
      $this->db->bind_param(":uid", $this->uid, 'integer');
    }

    $record = $this->db->fetch_first_object();
    $data['mid'] = ($record) ? $record->mid : "";
    $data['map'] = ($record) ? @unserialize($record->map) : "";
    $data['status'] = ($data['map']) ? 'ok' : 'failed';

    Header::set_header('json');
    echo json_encode($data);
  }

  /*
  * Implemented create method
  */
  public function create() {
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
        uid = :uid AND title = :title";
    $this->db->prepare($sql);
    $this->bind_param(":uid", $this->uid, 'integer');
    $this->bind_param(":title", $data['title'], 'string');
    $record = $this->db->query_first_object($sql);

    $output = array();
    $output['status'] = "ok";

    if($record) {
      unset($data['created']);
      $this->db->query_update('maps', $data, 'mid='.$record->mid);
      $output['mid'] = $record->mid;
    } else {
      $output['mid'] = $this->db->query_insert('maps', $data);
    }

    Header::set_header('json');
    echo json_encode($output);
  }

  /*
  * Implemented update method
  */
  public function update() {
    $this->not_implemented();
  }

  /*
  * Implemented destroy method
  */
  public function destroy($id) {
    $sql = "
      DELETE 
      FROM
        maps
      WHERE 
        mid = :mid";
    if(User::$roles[$this->role] == 'administrator') {
      $this->db->prepare($sql);
      $this->db->bind_param(":mid", $id, 'integer');
    } else {
      $sql .= " AND uid = :uid";
      $this->db->prepare($sql);
      $this->db->bind_param(":mid", $id, 'integer');
      $this->db->bind_param(":uid", $this->uid, 'integer');
    }
    $this->db->execute();
    Header::set_header('json');
    echo json_encode(array("status" => "ok"));
  }

  private function produce_output($rows) {

    $output = '';
    if($this->total->total > 0) {
      $data_uid = '';
      $output .= '<table class="grid-usermaps">' . "\n";
      $output .= '<thead>' . "\n";
      $output .= '<tr>' . "\n";
      if($this->uid_q) {
        $header_count = sprintf(_("%d of %d for %s"), $this->db->row_count(), $this->total->total, $this->total->username);
        $data_uid = " data-uid=".$this->uid_q;
      } else {
        $header_count = sprintf(_("%d of %d"), $this->db->row_count(), $this->total->total);
      }
      $output .= '<th class="left-align">'._("Title").' <input type="text" id="filter-mymaps" size="25" maxlength="35" value="" name="filter-mymap"'.$data_uid.' /> '.$header_count.'</th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "created" && isset($_GET['dir'])) ? " ".$this->dir : "";
      if(!isset($_GET['sort']) && !isset($_GET['dir'])) { $sort_dir = " desc"; }
      $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="created" href="#">'._("Created").'</a></th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "updated" && isset($_GET['dir'])) ? " ".$this->dir : "";
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
      foreach($rows as $row) {
        $class = ($i % 2) ? 'class="even"' : 'class="odd"';
        $output .= '<tr '.$class.'>';
        $output .= '<td class="title">';
        $output .= (User::$roles[$this->role] == 'administrator' && !$this->uid_q) ? $row->username . ': ' : '';
        $output .= '<a class="map-load" data-id="'.$row->mid.'" href="#">' . Utilities::check_plain(stripslashes($row->title)) . '</a>';
        $output .= '</td>';
        $output .= '<td class="center-align">' . gmdate("M d, Y", $row->created) . '</td>';
        $output .= '<td class="center-align">';
        $output .= ($row->updated) ? gmdate("M d, Y", $row->updated) : ' - ';
        $output .= '</td>';
        $output .= '<td class="actions">';
        if($this->uid == $row->uid || User::$roles[$this->role] == 'administrator') {
          $output .= '<a class="sprites-before map-delete" data-id="'.$row->mid.'" href="#">'._("Delete").'</a>';
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

    Header::set_header('html');
    echo $output;
  }

}