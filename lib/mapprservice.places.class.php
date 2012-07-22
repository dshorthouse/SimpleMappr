<?php

/**************************************************************************

File: mapprservice.places.class.php

Description: Base map class for SimpleMappr. 

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

class PLACES {

  private $_request;

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
    return $this;
  }

  /*
  * Utility method
  */
  private function execute() {
    $this->_request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
    $this->_db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    $this->restful_action();
  }

  /*
  * Detect type of request and perform appropriate method
  */
  private function restful_action() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch($method) {
      case 'GET':
        $this->index_places();
      break;

      case 'POST':
        $this->index_places();
      break;

      default:
      break;
    }
  }

  private function index_places() {
    $result = array();
    $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : $this->_request[0];
    $where = isset($_REQUEST['filter']) ? " WHERE LOWER(country) LIKE LOWER('%".$this->_db->escape($_REQUEST['filter'])."%')" : null;
    $sql = "SELECT * FROM stateprovinces".$where." ORDER BY country, stateprovince";

    if($term) {
      $sql = "
        SELECT DISTINCT
          sp.country as label, sp.country as value
        FROM
          stateprovinces sp
        WHERE sp.country LIKE '".$this->_db->escape($term)."%'
        ORDER BY sp.country
        LIMIT 5";
      $result = $this->_db->fetch_all_array($sql);
      header("Content-Type: application/json");
      echo json_encode($result);
    } else {
      $rows = $this->_db->query($sql);
      header("Content-Type: text/html");
      $this->produce_output($rows);
    }
  }

  private function produce_output($rows) {
    $output  = "";
    $output .= '<table class="countrycodes">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<td class="title">'._("Country");
    $output .= '<input class="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" />';
    $output .= '</td>';
    $output .= '<td class="code">ISO</td>';
    $output .= '<td class="title">'._("State/Province").'</td>';
    $output .= '<td class="code">'._("Code").'</td>';
    $output .= '<td class="example">'._("Example").'</td>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    if($this->_db->affected_rows > 0) {
      $i = 0;
      while ($record = $this->_db->fetch_array($rows)) {
        $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
        $output .= "<tr ".$class.">";
        $output .= "<td>" . $record['country'] . "</td>";
        $output .= "<td>" . $record['country_iso'] . "</td>";
        $output .= "<td>" . $record['stateprovince'] . "</td>";
        $output .= "<td>" . $record['stateprovince_code'] . "</td>";
        $example = ($record['stateprovince_code']) ? $record['country_iso'] . "[" . $record['stateprovince_code'] . "]" : "";
        $output .= "<td>" . $example . "</td>";
        $output .= "</tr>" . "\n";
        $i++;
      }
    } else {
     $output .= "<tr class=\"odd\"><td colspan=\"5\">"._("Nothing found")."</td></tr>";
    }
    $output .= '</tbody>';
    $output .= '</table>';
    echo $output;
  }

}
?>