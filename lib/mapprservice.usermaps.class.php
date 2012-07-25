<?php

/**************************************************************************

File: mapprservice.usermaps.class.php

Description: Executes actions on user maps

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('db.class.php');
require_once('mapprservice.usersession.class.php');

class USERMAPS {

  private $_uid;

  private $_request;

  private $_uid_q;

  private $_db;

  function __construct() {
    USERSESSION::select_locale();
    $this->set_header()
         ->execute();
  }

  /*
  * Set header to prevent caching
  */
  private function set_header() {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    session_start();
    return $this;
  }

  /*
  * Utility method
  */
  private function execute() {
    if(!isset($_SESSION['simplemappr'])) {
      header("Content-Type: application/json");
      echo "{ \"error\" : \"session timeout\" }";
      exit;
    } else {
      $this->_uid = $_SESSION['simplemappr']['uid'];
      $this->_request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
      $this->_uid_q = isset($_GET['uid']) ? $_GET['uid'] : null;
      $this->_db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
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
        if(is_numeric($this->_request[0])) {
          $this->show_map();
        } else {
          $this->index_maps();
        }
      break;

      case 'POST':
        $data = array(
          'uid' => $this->_uid,
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
            uid=".$this->_db->escape($this->_uid)." AND title='".$this->_db->escape($data['title'])."'";
        $record = $this->_db->query_first($sql);

        if($record['mid']) {
          unset($data['created']);
          $this->_db->query_update('maps', $data, 'mid='.$record['mid']);
          $mid = $record['mid'];
        } else {
          $mid = $this->_db->query_insert('maps', $data);
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
    $dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
    $order = "m.created ".$dir;

    if($this->_uid != 1) {
      $where['user'] =  "WHERE m.uid = ".$this->_db->escape($this->_uid);
    }
    if($this->_uid_q) {
      $where['user'] = "WHERE m.uid = ".$this->_db->escape($this->_uid_q);
    }

    $sql = "
      SELECT
        u.username AS username, COUNT(m.mid) AS total
      FROM
        maps m
      INNER JOIN
        users u ON (m.uid = u.uid)
      ".implode("",$where);

    $total = $this->_db->query_first($sql);

    $b = "";
    if(isset($_GET['q'])) {
      if($this->_uid == 1 && !$this->_uid_q) { $b = "WHERE "; }
      $where['where'] = $b."LOWER(m.title) LIKE '%".$this->_db->escape($_GET['q'])."%'";
      if($this->_uid == 1) {
        $where['where'] .= " OR LOWER(u.username) LIKE '%".$this->_db->escape($_GET['q'])."%'";
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

    $rows = $this->_db->query($sql);

    if($total['total'] > 0) {
      $output .= '<table class="grid-usermaps">' . "\n";
      $output .= '<thead>' . "\n";
      $output .= '<tr>' . "\n";
      if($this->_uid_q) {
        $header_count = sprintf(_("%d of %d for %s"), $this->_db->affected_rows, $total['total'], $total['username']);
      } else {
        $header_count = sprintf(_("%d of %d"), $this->_db->affected_rows, $total['total']);
      }
      $header_count = ($this->_uid_q) ? : 
      $output .= '<th class="left-align">'._("Title").' <input type="text" id="filter-mymaps" size="25" maxlength="35" value="" name="filter-mymap" /> '.$header_count.'</th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "created" && isset($_GET['dir'])) ? " ".$dir : "";
      if(!isset($_GET['sort']) && !isset($_GET['dir'])) { $sort_dir = " desc"; }
      $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="created" href="#">'._("Created").'</a></th>';
      $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "updated" && isset($_GET['dir'])) ? " ".$dir : "";
      $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="updated" href="#">'._("Updated").'</th>';
      $output .= '<th class="actions">'._("Actions");
      if($this->_uid == 1) {
        $output .= '<a href="#" class="sprites-after toolsRefresh"></a>';
      }
      $output .= '</th>';
      $output .= '</tr>' . "\n";
      $output .= '</thead>' . "\n";
      $output .= '<tbody>' . "\n";
      $i=0;
      while ($record = $this->_db->fetch_array($rows)) {
        $class = ($i % 2) ? 'class="even"' : 'class="odd"';
        $output .= '<tr '.$class.'>';
        $output .= '<td class="title">';
        $output .= ($this->_uid == 1 && !$this->_uid_q) ? $record['username'] . ': ' : '';
        $output .= stripslashes($record['title']);
        $output .= '</td>';
        $output .= '<td class="center-align">' . gmdate("M d, Y", $record['created']) . '</td>';
        $output .= '<td class="center-align">';
        $output .= ($record['updated']) ? gmdate("M d, Y", $record['updated']) : ' - ';
        $output .= '</td>';
        $output .= '<td class="actions">';
        $output .= '<a class="sprites-before map-load" data-mid="'.$record['mid'].'" href="#">'._("Load").'</a>';
        if($this->_uid == $record['uid'] || $this->_uid == 1) {
          $output .= '<a class="sprites-before map-delete" data-mid="'.$record['mid'].'" href="#">'._("Delete").'</a>';
        }
        $output .= '</td>';
        $output .= '</tr>' . "\n";
        $i++;
      }
      $output .= '</tbody>' . "\n";
      $output .= '</table>' . "\n";

      $dir = ($dir == "desc") ? "asc" : "desc";

      $output .= '<script type="text/javascript">
        function loadList(self) {
          var data = {};
          data.uid = '.$this->_uid_q.';
          $.each($(".ui-icon-triangle-sort", ".grid-usermaps"), function() {
            if($(this).hasClass("asc")) {
              data.sort = { item : $(this).attr("data-sort"), dir : "asc" };
            } else if ($(this).hasClass("desc")) {
              data.sort = { item : $(this).attr("data-sort"), dir : "desc" };
            }
          });
          if(self.value.length !== 0) { data.q = self.value; }
          Mappr.loadMapList(data);
        }
        $(".toolsRefresh", ".grid-usermaps").click(function(e) {
          e.preventDefault();
          Mappr.loadMapList();
        });
        $(".ui-icon-triangle-sort", ".grid-usermaps").click(function(e) {
          e.preventDefault();
          var data = {};
          data.uid = '.$this->_uid_q.';
          data.sort = { item : $(this).attr("data-sort"), dir : "'.$dir.'" };
          if($("#filter-mymaps").val().length !== 0) {
            data.q = $("#filter-mymaps").val();
          }
          Mappr.loadMapList(data);
        });
        $("#filter-mymaps")
          .keypress(function(e) { if (e.which === 13) { loadList(this); } })
          .blur(function(e) { loadList(this); });</script>';
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
    if(!$this->_uid == 1) { $where = ' AND uid = "'.$this->_db->escape($this->_uid).'"'; }
    $sql = '
      SELECT
        mid, map
      FROM 
        maps
      WHERE
        mid="'.$this->_db->escape($this->_request[0]) . '"'.$where;
    $record = $this->_db->query_first($sql);

    $data['status'] = 'ok';
    $data['mid'] = $record['mid'];
    $data['map'] = unserialize($record['map']);

    header("Content-Type: application/json");
    echo json_encode($data);
  }

  /*
  * Destroy method to delete a map
  */
  private function destroy_map() {
    $where = 'mid='.$this->_db->escape($this->_request[0]);
    if($this->_uid != 1) {
      $where .= ' AND uid = '.$this->_db->escape($this->_uid);
    }
    $sql = '
      DELETE 
      FROM
        maps
      WHERE 
        '.$where;
    $this->_db->query($sql);

    header("Content-Type: application/json");
    echo '{"status":"ok"}';
  }

}

?>