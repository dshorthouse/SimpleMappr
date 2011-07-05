<?php
require_once('../conf/conf.php');
require_once('../conf/conf.db.php');
require_once('../includes/db.class.php');

session_start();

if(!isset($_SESSION['simplemappr']) || !isset($_GET['action'])) {
    header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
    exit;
}

$uid = $_SESSION['simplemappr']['uid'];

global $db;
$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

switch($_GET['action']) {
    case 'logout':
        session_unset();
        session_destroy();
        setcookie("simplemappr", "", time() - 3600, "/");
        header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
        exit;
    break;
    
    case 'list':
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
            $output .= "<td class=\"left-align\">Title <input type=\"text\" id=\"filter-mymaps\" size=\"25\" maxlength=\"35\" value=\"\" name=\"filter-mymap\" /></td>";
            $output .= "<td class=\"actions\">Actions</td>";
            $output .= "</tr>" . "\n";
            $output .= "</thead>" . "\n";
            $output .= "<tbody>" . "\n";
            $i=0;
            while ($record = $db->fetch_array($rows)) {
              $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
              $output .= "<tr ".$class.">";
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

            $output .= "<script type=\"text/javascript\">
              $(\"#filter-mymaps\")
                .keyup(function() { $.uiTableFilter( $('#usermaps table'), this.value ); })
                .keypress(function(event) { if (event.which === 13) { return false; }
              });</script>";
        }
        else {
            $output .= '<div id="mymaps" class="panel"><p>Start by adding data on the "Point Data" or "Regions" tabs, press the Preview buttons there, then save your map from the top bar of the "Preview" tab.</p><p>Alternatively, you may create and save a generic template by setting the extent, projection, and layer options you like without adding point data or specifying what political regions to shade.</p></div>';
        }
        
        echo $output;
    break;
    
    case 'save':
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
        echo "{\"status\":\"ok\", \"mid\":\"" . $mid . "\"}";
    break;
    
    case 'load':
        $where = "";
        if(!$uid == 1) $where = " AND uid = ".$db->escape($uid);
        if($_GET['map']) {
            $sql = "
            SELECT
                mid, map
            FROM 
                maps
            WHERE
                 mid=".$db->escape($_GET['map']) . $where;
            $record = $db->query_first($sql);
            
            $data['status'] = "ok";
            $data['mid'] = $record['mid'];
            $data['map'] = unserialize($record['map']);
            
            echo json_encode($data);
        }
    break;
    
    case 'delete':
        if($_GET['map']) {
            $sql = "
            DELETE 
            FROM maps
            WHERE 
              uid=".$db->escape($uid)." AND mid=".$db->escape($_GET['map']);
            $db->query($sql);
            
            echo "{\"status\":\"ok\"}";
        }
    break;
    
    case 'users':
        if($uid == 1) {
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
    break;
}

?>