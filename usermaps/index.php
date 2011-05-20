<?php
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

$db->connect();

switch($_GET['action']) {
	case 'logout':
		session_unset();
		session_destroy();
		$db->close();
		header('Location: http://' . $_SERVER['SERVER_NAME'] . '');
		exit;
	break;
	
	case 'list':
		$output = '';
		
		$sql = "
		SELECT
		  m.mid,
		  m.title 
		FROM 
		  maps m 
		WHERE  
		  m.uid = ".$db->escape($uid)."
		ORDER BY m.created DESC";

		$rows = $db->query($sql);
		
		if($db->affected_rows > 0) {
			$output .= "<table>" . "\n";
			$output .= "<thead>" . "\n";
			$output .= "<tr>" . "\n";
			$output .= "<th>Title</th>";
			$output .= "<th class=\"actions\">Actions</th>";
			$output .= "</tr>" . "\n";
			$output .= "</thead>" . "\n";
			$output .= "<tbody>" . "\n";
			$i=0;
			while ($record = $db->fetch_array($rows)) {
			  $class = ($i % 2) ? "class=\"even\"" : "class=\"odd\"";
			  $output .= "<tr ".$class."><td class=\"title\">" . stripslashes($record['title']) . "</td>";
			  $output .= "<td class=\"actions\">";
			  $output .= "<a class=\"map-load\" rel=\"".$record['mid']."\" href=\"#\" onclick=\"return false;\">Load</a>";
//			  $output .= "<a class=\"map-url\" rel=\"".$record['mid']."\" href=\"#\" onclick=\"return false;\">URL</a>";
			  $output .= "<a class=\"map-delete\" rel=\"".$record['mid']."\" href=\"#\" onclick=\"return false;\">Delete</a>";
			  $output .= "</td>";
			  $output .= "</tr>" . "\n";
			  $i++;
			}
			$output .= "</tbody>" . "\n";
			$output .= "</table>" . "\n";
		}
		else {
			$output .= '<div id="mymaps" class="panel"><p>Start by adding data on the "Data Layers" or "Shaded Regions" tabs, press the Preview buttons there, then save your map on the "Map Preview" tab.</p><p>Alternatively, you may create and save a generic template by setting the extent, projection, and layer options you like without adding point data or specifying what political regions to shade.</p></div>';
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
		}
		else {
			$db->query_insert('maps', $data);
		}
		echo "{\"status\":\"ok\"}";
	break;
	
	case 'load':
		if($_GET['map']) {
			$sql = "
			SELECT
				map
			FROM 
				maps
			WHERE
				uid=".$db->escape($uid)." AND mid=".$db->escape($_GET['map']);
			$record = $db->query_first($sql);
			
			$data['status'] = "ok";
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
}

$db->close();

?>