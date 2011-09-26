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

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

global $uid, $request, $db;
$uid = $_SESSION['simplemappr']['uid'];
$request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
      if(is_numeric($request[0])) {
        getMap();
      } else {
        getList();
      }
    break;

    case 'POST':
      $data = array(
          'uid' => $uid,
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
        uid=".$db->escape($uid)." AND title='".$db->escape($data['title'])."'";
      $record = $db->query_first($sql);
      
      if($record['mid']) {
        $db->query_update('maps', $data, 'mid='.$record['mid']);
        $mid = $record['mid'];
      }
      else {
        $mid = $db->query_insert('maps', $data);
      }

      header("Content-Type: application/json");
      echo "{\"status\":\"ok\", \"mid\":\"" . $mid . "\"}";
    break;

    case 'DELETE':
      $sql = "
        DELETE 
        FROM maps
        WHERE 
          uid=".$db->escape($uid)." AND mid=".$db->escape($request[0]);
      $db->query($sql);

      header("Content-Type: application/json");
      echo "{\"status\":\"ok\"}";
    break;

   default:
   break;
}

function getList() {
  global $uid, $db;

      $where = '';
      $output = '';
      
      if($uid != 1) $where =  " WHERE m.uid = ".$db->escape($uid);
      
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

      $rows = $db->query($sql);
      
      if($db->affected_rows > 0) {
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
          while ($record = $db->fetch_array($rows)) {
            $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
            $output .= "<tr ".$class.">";
//              $output .= "<td class=\"download\"><input type=\"checkbox\" class=\"download-checkbox\" name=\"download[".$record['mid']."]\" /></td>";
            $output .= "<td class=\"title\">";
            $output .= ($uid == 1) ? $record['username'] . " (" . gmdate("M d, Y", $record['created']) . "): <em>" : "";
            $output .= stripslashes($record['title']);
            $output .= ($uid == 1) ? "</em>" : "";
            $output .= "</td>";
            $output .= "<td class=\"actions\">";
            $output .= "<a class=\"sprites map-load\" data-mid=\"".$record['mid']."\" href=\"#\">Load</a>";
            if($uid == $record['uid']) {
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
        $output .= '<div id="mymaps" class="panel"><p>Start by adding data on the "Point Data" or "Regions" tabs, press the Preview buttons there, then save your map from the top bar of the "Preview" tab.</p><p>Alternatively, you may create and save a generic template by setting the extent, projection, and layer options you like without adding point data or specifying what political regions to shade.</p></div>';
      }

      header("Content-Type: text/html");
      echo $output;
}

function getMap() {
  global $uid, $db, $request;

   $where = "";
   if(!$uid == 1) { $where = " AND uid = ".$db->escape($uid); }
   $sql = "
   SELECT
       mid, map
   FROM 
       maps
   WHERE
        mid=".$db->escape($request[0]) . $where;
   $record = $db->query_first($sql);
   
   $data['status'] = "ok";
   $data['mid'] = $record['mid'];
   $data['map'] = unserialize($record['map']);

   header("Content-Type: application/json");            
   echo json_encode($data);
}

?>