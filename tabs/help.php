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
    
    <div class="panel">
        <p>This application makes heavy use of JavaScript. A modern browser like Internet Explorer 9, FireFox 5, Google Chrome, or Safari 5 is strongly recommended.</p>
    </div>
    
    <h2>Preview</h2>
    <p>Use the <em>Preview</em> tab to refine your eventual map export by adjusting various options, downloading the result, or saving it for later re-use (when logged in)</p>

    <p><strong>Toolbar buttons:</strong></p>
        <ul class="toolbar">
            <li class="sprites toolsZoomIn">Zoom in: click this icon to draw a zoom region on the preview</li>
            <li class="sprites toolsZoomOut">Zoom out: click this icon to zoom out one step</li>
            <li class="sprites rotateAnticlockwise">Rotate counter-clockwise: select 5<sup>o</sup>, 10<sup>o</sup>, and 15<sup>o</sup> from the drop-down menu while hovering on this icon</li>
            <li class="sprites rotateClockwise">Rotate clockwise: as above</li>
            <li class="sprites toolsCrop">Crop: click this icon to draw an expandable, square-shaped rubber band that precisely defines a cropped portion of the map you wish to appear in the exported map</li> 
            <li class="sprites toolsQuery">Fill regions: click this icon to choose a color then draw an expandable, square-shaped rubber band that will shade States and Provinces (if layer visible) or Countries bound within. Selected areas are added under the Regions tab.</li>
<!--
            <li class="sprites toolsDraw">Draw shape: click this icon to draw an free-hand line. A freehand drawing gets automatically added to and replaces the contents of Freehand 1 form under the Freehand tab.</li>
-->
            <li class="sprites toolsRefresh">Refresh: refresh the map image</li>
            <li class="sprites toolsRebuild">Rebuild: re-render the default presentation at lowest zoom and geographic projection</li>
        </ul>
    <p><strong>Layers:</strong></p>
        <ul>
            <li>State/Provinces borders: select this checkbox to draw all State and Province borders for all countries</li>
            <li>place names: select this checkbox to overlay place names</li>
            <li>physical labels: select this checkbox to overlay labels for physical features</li>
            <li>marine labels: select this checkbox to overlay labels for marine features</li>
            <li>lakes (filled): select this checkbox to overlay lakes as greyscale polygons</li>
            <li>lakes (outline): select this checkbox to overlay lakes as black outlines</li>
            <li>rivers: select this checkbox to overlay rivers as black outlines</li>
            <li>shaded relief: select this checkbox to render a color, shaded relief layer</li>
            <li>shaded relief (greyscale): select this checkbox to render a greyscale, shaded relief layer</li>
        </ul>
    <p><strong>Options:</strong></p>
        <ul>
            <li>scalebar: select this checkbox to draw a scalebar at the bottom on the map</li>
            <li>graticules: select this checkbox to draw a graticule (grid) layer on the map</li>
        </ul>
    <p><strong>Projection:</strong></p>
        <ul>
            <li>Choose among several projections. [Hint: first use zoom while on the base geographic projection for best effects]</li>
        </ul>
    
    <ul class="toolbar">
        <li class="sprites toolsSave">While logged in, click this icon to give your map a title and save its settings for later reuse from the <em>My Maps</em> tab.</li>
        <li class="sprites toolsDownload">Download the map as web-friendly png, high resolution tif, eps, or scalable vector graphic (svg). The latter is recommended for the preparation of figures in manuscripts because it is lossless. However, the svg download does not include a scalebar, legend, or shaded relief layer(s) because these are raster-based.</li>
    </ul>
    
    <h2>Point Data</h2>
    <p>Use the <em>Point Data</em> tab to paste coordinates as <em>latitude, longitude</em> on separate lines and select the marker shape, size, and color.</p>
    
    <div>
    <p><strong>Coordinate format:</strong> <em>e.g.</em> in western hemisphere above equator 45.55, -120.25; in western hemisphere below equator -15.66, -65.10; eastern hemisphere above equator 64.82, 75.1</p>
    <div id="example-data">
      <img src="../public/images/help_data.png" alt="Example Data Entry" />
      <img src="../public/images/38100.png" alt="38,-100 (North America)" />
      <img src="../public/images/25140.png" alt="-25,140 (Australia)" />
    </div>
    <p><strong>Pushpin color:</strong> The pushpin colors are configured using the RGB color scheme and a color selector is provided. By default, "0 0 0" (black) is selected. Shades of gray may be configured by typing variations of identically numbered triples. For example, "10 10 10" is dark gray whereas "100 100 100" is a lighter shade of gray.</p>
    </div>
    
    <h2>Regions</h2>
    <p>Use the <em>Regions</em> tab to list political regions you would like shaded and select the shade color. Separate each political region by a comma or semicolon. Alternatively, you may use State/Province codes such as USA[WY|WA|MT], CAN[AB BC] that will shade Wyoming, Washington, Montana, Alberta, and British Columbia. Notice that States or Provinces are separated by a space or a pipe and these are wrapped with square brackets, prefixed with the three-letter ISO country code.</p>

    <table id="countrycodes">
      <thead>
        <tr>
          <td class="title">Country
            <input id="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" />
          </td>
          <td class="code">ISO</td>
          <td class="title">State/Province</td>
          <td class="code">Code</td>
          <td class="example">Example</td>
        </tr>
      </thead>
      <tbody>
      <?php
        echo $output;
      ?>
      </tbody>
    </table>

<!--
    <h2>Freehand</h2>
    <p>Use the <em>Freehand</em> tab to record freehand drawing data represented as Well-known Text (WKT) and to adjust the color of the rendered line, circle, or polygon. A few examples are: POLYGON((-103 54,-111 51,-100 49,-103 54)) and  LINESTRING(-76 35,-76 35,-76 36,-76 36,-91 40)</p>
-->  
</div>