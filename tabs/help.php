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
<script type="text/javascript">
$("#filter-countries")
  .keyup(function() { $.uiTableFilter( $('#countrycodes'), this.value ); })
  .keypress(function(event) { if (event.which === 13) { return false; }
});
</script>
<!-- help tab -->
<div id="map-help">
    
  <div class="panel ui-corner-all">
    <p><?php echo _("This application makes heavy use of JavaScript. A modern browser like Internet Explorer 9, FireFox 6+, Google Chrome, or Safari 5+ is strongly recommended."); ?></p>
  </div>

  <div class="header">
    <h2><?php echo _("Preview"); ?></h2>
  </div>

  <p><?php echo _("Use the Preview tab to refine your eventual map export by adjusting various options, downloading the result, or saving it for later re-use (when logged in)"); ?></p>

  <h3><?php echo _("Toolbar buttons"); ?></h3>
  <dl class="toolbar ui-helper-clearfix">
    <dt class="sprites-before toolsZoomIn"><?php echo _("Zoom in"); ?>:</dt>
    <dd><?php echo _("draw a zoom region on the preview"); ?></dd>

    <dt class="sprites-before toolsZoomOut"><?php echo _("Zoom out"); ?>:</dt>
    <dd><?php echo _("zoom out one step"); ?></dd>

    <dt class="sprites-before toolsCrop"><?php echo _("Crop"); ?>:</dt>
    <dd><?php echo _("draw an expandable, square-shaped rubber band that precisely defines a cropped portion of the map you wish to appear in the exported map. Typing precise coordinates in the corner boxes within the crop window are not always retained after the crop window is automatically redrawn because your computer monitor has a finite number of pixels."); ?></dd>

    <dt class="sprites-before toolsQuery"><?php echo _("Fill regions"); ?>:</dt>
    <dd><?php echo _("choose a color then draw an expandable, square-shaped rubber band to shade States/Provinces (if layer visible) or Countries. Selections are sequentially added in the Regions tab."); ?></dd>

    <dt class="sprites-before toolsRefresh"><?php echo _("Refresh"); ?>:</dt>
    <dd><?php echo _("refresh the map image"); ?></dd>

    <dt class="sprites-before toolsRebuild"><?php echo _("Rebuild"); ?>:</dt>
    <dd><?php echo _("re-render the default presentation at lowest zoom and geographic projection"); ?></dd>
  </dl>

  <h3><?php echo _("Layers"); ?></h3>
  <dl class="ui-helper-clearfix">
    <dt><?php echo _("State/Provinces"); ?>:</dt>
    <dd><?php echo _("select this checkbox to draw all State and Province borders for all countries"); ?></dd>

    <dt><?php echo _("lakes (outline)"); ?>:</dt>
    <dd><?php echo _("select this checkbox to overlay lakes as black outlines"); ?></dd>

    <dt><?php echo _("lakes (greyscale)"); ?>:</dt>
    <dd><?php echo _("select this checkbox to overlay lakes as greyscale polygons"); ?></dd>

    <dt><?php echo _("rivers"); ?>:</dt>
    <dd><?php echo _("select this checkbox to overlay rivers as black outlines"); ?></dd>

    <dt><?php echo _("relief"); ?>:</dt>
    <dd><?php echo _("select this checkbox to render a color, shaded relief layer"); ?></dd>

    <dt><?php echo _("relief (greyscale)"); ?>:</dt>
    <dd><?php echo _("select this checkbox to render a greyscale, shaded relief layer"); ?></dd>
  </dl>

  <h3><?php echo _("Labels"); ?></h3>
  <dl class="ui-helper-clearfix">
    <dt><?php echo _("Countries"); ?>:</dt>
    <dd><?php echo _("label countries"); ?></dd>

    <dt><?php echo _("State/Provinces"); ?>:</dt>
    <dd><?php echo _("label States and Provinces"); ?></dd>

    <dt><?php echo _("lakes"); ?>:</dt>
    <dd><?php echo _("label lakes"); ?></dd>

    <dt><?php echo _("rivers"); ?>:</dt>
    <dd><?php echo _("label rivers"); ?></dd>

    <dt><?php echo _("places"); ?>:</dt>
    <dd><?php echo _("label city, town names"); ?></dd>

    <dt><?php echo _("physical"); ?>:</dt>
    <dd><?php echo _("label physical features"); ?></dd>

    <dt><?php echo _("marine"); ?>:</dt>
    <dd><?php echo _("label marine features"); ?></dd>
  </dl>

  <h3><?php echo _("Options"); ?></h3>
  <dl class="ui-helper-clearfix">
    <dt><?php echo _("graticules"); ?>:</dt>
    <dd><?php echo _("draw a graticule (grid) layer on the map using either fixed, 5, or 10 degree spacing"); ?></dd>
  </dl>

  <h3><?php echo _("Projection"); ?></h3>
  <dl class="ui-helper-clearfix">
    <dt><?php echo _("projection"); ?>:</dt>
    <dd><?php echo _("Choose among several projections. [Hint: first use zoom while on the base geographic projection for best effects]"); ?></dd>
  </dl>
    
    <dl class="toolbar ui-helper-clearfix">
      <dt class="sprites-before map-save"><?php echo _("Save"); ?>:</dt>
      <dd><?php echo _("while logged in, click this icon to give your map a title and save its settings for later reuse from the My Maps tab."); ?></dd>

      <dt class="sprites-before map-embed"><?php echo _("Embed"); ?>:</dt>
      <dd><?php echo _("once a map is saved, click this icon to obtain a URL for embedding on other websites."); ?></dd>

      <dt class="sprites-before map-download"><?php echo _("Download"); ?>:</dt>
      <dd><?php echo _("choose from a web-friendly png, high resolution tif, pptx (PowerPoint), docx (Word), kml (Google Earth) or scalable vector graphic (svg). The latter is recommended for the preparation of figures in manuscripts because it is lossless. However, the svg download does not include a scalebar, legend, or shaded relief layer(s) because these are raster-based."); ?></dd>
    </dl>

    <div class="header">
      <h2><?php echo _("Point Data"); ?></h2>
    </div>
    <p><?php echo _("Use the Point Data tab to paste coordinates as latitude, longitude on separate lines and select the marker shape, size, and color."); ?></p>

    <dl class="ui-helper-clearfix">
      <dt><?php echo _("Coordinate format"); ?>:</dt>
      <dd><?php echo _("in western hemisphere above equator 45.55, -120.25; in western hemisphere below equator -15.66, -65.10; eastern hemisphere above equator 64.82, 75.1"); ?></dd>
    </dl>
    <div id="example-data">
      <img src="../public/images/help-data.png" alt="<?php echo _("Example Data Entry"); ?>" width="400" height="215" />
      <img src="../public/images/38100.png" alt="<?php echo _("38,-100 (North America)"); ?>" width="200" height="100" />
      <img src="../public/images/25140.png" alt="<?php echo _("-25,140 (Australia)"); ?>" width="200" height="100" />
    </div>
    <dl class="ui-helper-clearfix">
      <dt><?php echo _("Pushpin color"); ?>:</dt>
      <dd><?php echo _("configured using the RGB color scheme and a color selector is provided. By default, \"0 0 0\" (black) is selected. Shades of gray may be configured by typing variations of identically numbered triples. For example, \"10 10 10\" is dark gray whereas \"100 100 100\" is a lighter shade of gray."); ?></dd>
    </dl>

    <div class="header">
      <h2><?php echo _("Regions"); ?></h2>
    </div>

    <p><?php echo _("Use the Regions tab to list political regions you would like shaded and select the shade color. Separate each political region by a comma or semicolon. Alternatively, you may use State/Province codes such as USA[WY|WA|MT], CAN[AB BC] that will shade Wyoming, Washington, Montana, Alberta, and British Columbia. Notice that States or Provinces are separated by a space or a pipe and these are wrapped with square brackets, prefixed with the three-letter ISO country code."); ?></p>

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

</div>