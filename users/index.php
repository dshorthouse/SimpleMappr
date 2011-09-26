<?php
require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('../lib/db.class.php');

session_start();

if(!isset($_SESSION['simplemappr'])) {
  header("Content-Type: application/json");
  echo "{ \"error\" : \"session timeout\" }";
  exit;
}

$uid = $_SESSION['simplemappr']['uid'];

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: text/html");

if($uid == 1) {
  $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
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
  
  $rows = $db->query($sql);
  
  $output = "";

  if($db->affected_rows > 0) {
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
      while ($record = $db->fetch_array($rows)) {
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

  echo $output;
}
?>