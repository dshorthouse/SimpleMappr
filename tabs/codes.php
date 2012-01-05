<?php
require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('../lib/db.class.php');
require_once('../lib/mapprservice.usersession.class.php');
USERSESSION::select_language();

$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
$sql = "SELECT * FROM stateprovinces ORDER BY country";
$rows = $db->query($sql);

$output = "";

if($db->affected_rows > 0) {
  $i=0;
  while ($record = $db->fetch_array($rows)) {
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
}
?>
<style type="text/css">
#countrycodes{background:none repeat scroll 0 0 #e6e6e6;border:1px solid gray;border-collapse:collapse;width:100%;color:#555;}
#countrycodes thead tr{height:1.75em;}
#countrycodes thead td{background-color:#e9e9e9;font-weight:normal;text-align:left;}
#countrycodes td.title{width:35%;}
#countrycodes td.code{width:60px;}
#countrycodes td.example{width:60px;}
#countrycodes tr{border:1px solid #aaa;}
#countrycodes tr.odd{background:none repeat scroll 0 0 #fff;}
</style>

    <table id="countrycodes">
      <thead>
        <tr>
          <td class="title"><?php echo _("Country"); ?>
            <input id="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" />
          </td>
          <td class="code">ISO</td>
          <td class="title"><?php echo _("State/Province"); ?></td>
          <td class="code"><?php echo _("Code"); ?></td>
          <td class="example"><?php echo _("Example"); ?></td>
        </tr>
      </thead>
      <tbody>
      <?php
        echo $output;
      ?>
      </tbody>
    </table>