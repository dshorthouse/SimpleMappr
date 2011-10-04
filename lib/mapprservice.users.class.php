<?php

/**************************************************************************

File: mapprservice.users.class.php

Description: Produces a list of users

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

class USERS {

  private $_uid;

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
      $this->_db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      $this->get_user_list();
    }
  }

  private function set_header() {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
  }

  private function get_user_list() {
    if($this->_uid == 1) {
      $sql = "
      SELECT
        u.username, u.email, u.access, count(m.mid) as num
      FROM
        users u
      LEFT JOIN
        maps m ON (u.uid = m.uid)
      GROUP BY
        u.username
      ORDER BY u.access DESC";

     $rows = $this->_db->query($sql);

     $output = "";

     if($this->_db->affected_rows > 0) {
       $output .= "<table>" . "\n";
       $output .= "<thead>" . "\n";
       $output .= "<tr>" . "\n";
       $output .= "<td class=\"left-align\">Username</td>";
       $output .= "<td class=\"left-align\">Email</td>";
       $output .= "<td>Maps</td>";
       $output .= "<td>Last Access</td>";
       $output .= "</tr>" . "\n";
       $output .= "</thead>" . "\n";
       $output .= "<tbody>" . "\n";
       $i=0;
       while ($record = $this->_db->fetch_array($rows)) {
         $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
         $output .= "<tr ".$class.">";
         $output .= "<td>" . stripslashes($record['username']) . "</td>";
         $output .= "<td>" . stripslashes($record['email']) . "</td>";
         $output .= "<td class=\"usermaps-center\">" . $record['num'] . "</td>";
         $access = ($record['access']) ? gmdate("M d, Y", $record['access']) : "-";
         $output .= "<td class=\"usermaps-center\">" . $access . "</td>";
         $output .= "</tr>" . "\n";
         $i++;
       }
       $output .= "</tbody>" . "\n";
       $output .= "</table>" . "\n";
     }

     header("Content-Type: text/html");
     echo $output;
    } else {
      header("Content-Type: application/json");
      echo "{ \"error\" : \"access denied\" }";
    }
  }

}

?>