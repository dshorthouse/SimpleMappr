<?php

/********************************************************************

places.class.php released under MIT License
Queries the database for country names on SimpleMappr

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
require_once($config_dir.'conf.db.php');
require_once('db.class.php');
require_once('session.class.php');

class Places {

  private $id;
  private $db;

  function __construct($id) {
    $this->id = $id;
    Session::select_locale();
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
    $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : $this->id;
    $where = isset($_REQUEST['filter']) ? " WHERE LOWER(country) LIKE LOWER('%".$this->db->escape($_REQUEST['filter'])."%')" : null;
    $sql = "SELECT * FROM stateprovinces".$where." ORDER BY country, stateprovince";

    if($term) {
      $sql = "
        SELECT DISTINCT
          sp.country as label, sp.country as value
        FROM
          stateprovinces sp
        WHERE sp.country LIKE '".$this->db->escape($term)."%'
        ORDER BY sp.country
        LIMIT 5";
      $result = $this->db->fetch_all_array($sql);
      header("Content-Type: application/json");
      echo json_encode($result);
    } else {
      $rows = $this->db->query($sql);
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
    if($this->db->affected_rows > 0) {
      $i = 0;
      while ($record = $this->db->fetch_array($rows)) {
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