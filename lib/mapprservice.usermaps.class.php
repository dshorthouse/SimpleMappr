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

class USERMAPS {

  private $_uid;

  private $_request;

  private $_db;

  function __construct() {
    $this->execute();
  }

  private function execute() {
    $this->set_header();
    if(!isset($_SESSION['simplemappr'])) {
      header("Content-Type: application/json");
      echo "{ \"error\" : \"session timeout\" }";
      exit;
    } else {
      $this->_uid = $_SESSION['simplemappr']['uid'];
      $this->_request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
      $this->_db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $this->restful_action();
    }
  }

  private function set_header() {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
  }

  private function restful_action() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
      case 'GET':
        if(is_numeric($this->_request[0])) {
          $this->get_map();
        } else {
          $this->get_list();
        }
      break;

      case 'POST':
        $data = array(
          'uid' => $this->_uid,
          'title' => $_POST['save']['title'],
          'map' => serialize($_POST),
          'created' => time(),
        );

        //first look to see if map by same title already exists
        $sql = "
        SELECT
          mid
        FROM maps
        WHERE
          uid=".$this->_db->escape($this->_uid)." AND title='".$this->_db->escape($data['title'])."'";
        $record = $this->_db->query_first($sql);

        if($record['mid']) {
          $this->_db->query_update('maps', $data, 'mid='.$record['mid']);
          $mid = $record['mid'];
        } else {
          $mid = $this->_db->query_insert('maps', $data);
        }

        header("Content-Type: application/json");
        echo "{\"status\":\"ok\", \"mid\":\"" . $mid . "\"}";
      break;

      case 'DELETE':
        $sql = "
        DELETE 
        FROM maps
        WHERE 
          uid=".$this->_db->escape($this->_uid)." AND mid=".$this->_db->escape($this->_request[0]);
        $this->_db->query($sql);

        header("Content-Type: application/json");
        echo "{\"status\":\"ok\"}";
      break;

      default:
      break;
    }
  }

  private function get_list() {
    $where = '';
    $output = '';

    if($this->_uid != 1) { $where =  " WHERE m.uid = ".$this->_db->escape($this->_uid); }

    $sql = "
    SELECT
      m.mid,
      m.title,
      m.created,
      u.email,
      u.uid,
      u.username 
    FROM 
      maps m 
    INNER JOIN
      users u ON (m.uid = u.uid)
    ".$where."
    ORDER BY m.created DESC";

    $rows = $this->_db->query($sql);

    if($this->_db->affected_rows > 0) {
      $output .= "<table>" . "\n";
      $output .= "<thead>" . "\n";
      $output .= "<tr>" . "\n";
//            $output .= "<td><input type=\"checkbox\" id=\"download-all\" name=\"download[all]\" /></td>";
      $output .= "<td class=\"left-align\">Title <input type=\"text\" id=\"filter-mymaps\" size=\"25\" maxlength=\"35\" value=\"\" name=\"filter-mymap\" /></td>";
      $output .= "<td class=\"actions\">Actions</td>";
      $output .= "</tr>" . "\n";
      $output .= "</thead>" . "\n";
      $output .= "<tbody>" . "\n";
      $i=0;
      while ($record = $this->_db->fetch_array($rows)) {
        $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
        $output .= "<tr ".$class.">";
//              $output .= "<td class=\"download\"><input type=\"checkbox\" class=\"download-checkbox\" name=\"download[".$record['mid']."]\" /></td>";
        $output .= "<td class=\"title\">";
        $output .= ($this->_uid == 1) ? $record['username'] . " (" . gmdate("M d, Y", $record['created']) . "): <em>" : "";
        $output .= stripslashes($record['title']);
        $output .= ($this->_uid == 1) ? "</em>" : "";
        $output .= "</td>";
        $output .= "<td class=\"actions\">";
        $output .= "<a class=\"sprites map-load\" data-mid=\"".$record['mid']."\" href=\"#\">Load</a>";
        if($this->_uid == $record['uid']) {
          $output .= "<a class=\"sprites map-delete\" data-mid=\"".$record['mid']."\" href=\"#\">Delete</a>";
        }
        $output .= "</td>";
        $output .= "</tr>" . "\n";
        $i++;
      }
      $output .= "</tbody>" . "\n";
      $output .= "</table>" . "\n";

/*
      $output .= "<fieldset>" . "\n";
      $output .= "<legend>File type</legend>" . "\n";

      $file_types = array('svg', 'png', 'tif', 'eps', 'kml');
      foreach($file_types as $type) {
        $checked = ($type == "svg") ? " checked=\"checked\"": "";
        $asterisk = ($type == "svg") ? "*" : "";
        $output .= "<input type=\"radio\" id=\"bulk-download-".$type."\" name=\"bulk-download-filetype\" value=\"".$type."\"".$checked." />";
        $output .=  "<label for=\"bulk-download-".$type."\">".$type.$asterisk."</label>";
      }

      $output .= "</fieldset>" . "\n";

      $output .= "<div><button class=\"sprites bulkdownload positive\">Download</button></div>";
*/
      $output .= "<script type=\"text/javascript\">
        Mappr.bindBulkDownload();
        $(\"#filter-mymaps\")
          .keyup(function() { $.uiTableFilter( $('#usermaps table'), this.value ); })
          .keypress(function(event) { if (event.which === 13) { return false; }
        });</script>";
    } else {
      $output .= '<div id="mymaps" class="panel ui-corner-all"><p>Start by adding data on the "Point Data" or "Regions" tabs, press the Preview buttons there, then save your map from the top bar of the "Preview" tab.</p><p>Alternatively, you may create and save a generic template by setting the extent, projection, and layer options you like without adding point data or specifying what political regions to shade.</p></div>';
    }

    header("Content-Type: text/html");
    echo $output;
  }

  private function get_map() {
   $where = "";
   if(!$this->_uid == 1) { $where = " AND uid = ".$this->_db->escape($this->_uid); }
   $sql = "
   SELECT
       mid, map
   FROM 
       maps
   WHERE
        mid=".$this->_db->escape($this->_request[0]) . $where;
   $record = $this->_db->query_first($sql);

   $data['status'] = "ok";
   $data['mid'] = $record['mid'];
   $data['map'] = unserialize($record['map']);

   header("Content-Type: application/json");            
   echo json_encode($data);
  }

}

?>