<?php
require_once('../config/conf.php');
require_once('../config/conf.db.php');
require_once('../lib/db.class.php');

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
        <p><?php echo _('This application makes heavy use of JavaScript. A modern browser like Internet Explorer 9, FireFox 6+, Google Chrome, or Safari 5+ is strongly recommended.'); ?></p>
    </div>

    <div class="header">
      <h2><?php echo _('Preview'); ?></h2>
    </div>
    <p><?php echo _('Use the <em>Preview</em> tab to refine your eventual map export by adjusting various options, downloading the result, or saving it for later re-use (when logged in)'); ?></p>

    <h3><?php echo _('Toolbar buttons'); ?></h3>
        <ul class="toolbar">
            <li><span class="sprites toolsZoomIn">&nbsp;</span><?php echo _('Zoom in: click this icon to draw a zoom region on the preview'); ?></li>
            <li><span class="sprites toolsZoomOut">&nbsp;</span><?php echo _('Zoom out: click this icon to zoom out one step'); ?></li>
            <li><span class="sprites toolsCrop">&nbsp;</span><?php echo _('Crop: click this icon to draw an expandable, square-shaped rubber band that precisely defines a cropped portion of the map you wish to appear in the exported map. Typing precise coordinates in the corner boxes within the crop window are not always retained after the crop window is automatically redrawn because your computer monitor has a finite number of pixels.'); ?></li> 
            <li><span class="sprites toolsQuery">&nbsp;</span><?php echo _('Fill regions: click this icon to choose a color then draw an expandable, square-shaped rubber band that will shade States and Provinces (if layer visible) or Countries bound within. Selected areas are added under the Regions tab.'); ?></li>
            <li><span class="sprites toolsRefresh">&nbsp;</span><?php echo _('Refresh: refresh the map image'); ?></li>
            <li><span class="sprites toolsRebuild">&nbsp;</span><?php echo _('Rebuild: re-render the default presentation at lowest zoom and geographic projection'); ?></li>
        </ul>

    <h3><?php echo _('Layers'); ?></h3>
        <ul>
            <li><?php echo _('State/Provinces: select this checkbox to draw all State and Province borders for all countries'); ?></li>
            <li><?php echo _('lakes (outline): select this checkbox to overlay lakes as black outlines'); ?></li>
            <li><?php echo _('lakes (greyscale): select this checkbox to overlay lakes as greyscale polygons'); ?></li>
            <li><?php echo _('rivers: select this checkbox to overlay rivers as black outlines'); ?></li>
            <li><?php echo _('relief: select this checkbox to render a color, shaded relief layer'); ?></li>
            <li><?php echo _('relief (greyscale): select this checkbox to render a greyscale, shaded relief layer'); ?></li>
        </ul>

    <h3><?php echo _('Labels'); ?></h3>
        <ul>
            <li><?php echo _('Countries: select this checkbox to label countries'); ?></li>
            <li><?php echo _('State/Provinces: select this checkbox to label States and Provinces'); ?></li>
            <li><?php echo _('lakes: select this checkbox to label lakes'); ?></li>
            <li><?php echo _('rivers: select this checkbox to label rivers'); ?></li>
            <li><?php echo _('places: select this checkbox to label place names'); ?></li>
            <li><?php echo _('physical: select this checkbox to label physical features'); ?></li>
            <li><?php echo _('marine: select this checkbox to label marine features'); ?></li>
        </ul>

    <h3><?php echo _('Options'); ?></h3>
        <ul>
            <li><?php echo _('graticules: select this checkbox to draw a graticule (grid) layer on the map using either fixed, 5<sup>o</sup>, or 10<sup>o</sup> spacing'); ?></li>
        </ul>

    <h3><?php echo _('Projection'); ?></h3>
        <ul>
            <li><?php echo _('Choose among several projections. [Hint: first use zoom while on the base geographic projection for best effects]'); ?></li>
        </ul>
    
    <ul class="toolbar">
        <li><span class="sprites toolsSave">&nbsp;</span><?php echo _('Save: while logged in, click this icon to give your map a title and save its settings for later reuse from the <em>My Maps</em> tab.'); ?></li>
        <li><span class="sprites toolsEmbed">&nbsp;</span><?php echo _('Embed: once a map is saved, click this icon to obtain a URL for embedding on other websites.'); ?></li>
        <li><span class="sprites toolsDownload">&nbsp;</span><?php echo _('Download: choose from a web-friendly png, high resolution tif, kml (Google Earth) or scalable vector graphic (svg). The latter is recommended for the preparation of figures in manuscripts because it is lossless. However, the svg download does not include a scalebar, legend, or shaded relief layer(s) because these are raster-based.'); ?></li>
    </ul>

    <div class="header">
      <h2><?php echo _('Point Data'); ?></h2>
    </div>
    <p><?php echo _('Use the <em>Point Data</em> tab to paste coordinates as <em>latitude, longitude</em> on separate lines and select the marker shape, size, and color.'); ?></p>
    
    <div>
    <p><strong><?php echo _('Coordinate format:</strong> <em>e.g.</em> in western hemisphere above equator 45.55, -120.25; in western hemisphere below equator -15.66, -65.10; eastern hemisphere above equator 64.82, 75.1'); ?></p>
    <div id="example-data">
      <img src="../public/images/help_data.png" alt="Example Data Entry" />
      <img src="../public/images/38100.png" alt="38,-100 (North America)" />
      <img src="../public/images/25140.png" alt="-25,140 (Australia)" />
    </div>
    <p><?php echo _('<strong>Pushpin color:</strong> The pushpin colors are configured using the RGB color scheme and a color selector is provided. By default, "0 0 0" (black) is selected. Shades of gray may be configured by typing variations of identically numbered triples. For example, "10 10 10" is dark gray whereas "100 100 100" is a lighter shade of gray.'); ?></p>
    </div>

    <div class="header">
      <h2><?php echo _('Regions'); ?></h2>
    </div>
    <p><?php echo _('Use the <em>Regions</em> tab to list political regions you would like shaded and select the shade color. Separate each political region by a comma or semicolon. Alternatively, you may use State/Province codes such as USA[WY|WA|MT], CAN[AB BC] that will shade Wyoming, Washington, Montana, Alberta, and British Columbia. Notice that States or Provinces are separated by a space or a pipe and these are wrapped with square brackets, prefixed with the three-letter ISO country code.'); ?></p>

    <table id="countrycodes">
      <thead>
        <tr>
          <td class="title"><?php echo _('Country'); ?>
            <input id="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" />
          </td>
          <td class="code">ISO</td>
          <td class="title"><?php echo _('State/Province'); ?></td>
          <td class="code"><?php echo _('Code'); ?></td>
          <td class="example"><?php echo _('Example'); ?></td>
        </tr>
      </thead>
      <tbody>
      <?php
        echo $output;
      ?>
      </tbody>
    </table>

</div>