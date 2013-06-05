<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root.'/lib/session.class.php');
Session::select_locale();
?>
<style type="text/css">
#map-help h3{font-size:0.75em;}
#map-help p{font-size:0.75em;}
#map-help dl{font-size:0.75em;}
#map-help dt{float:left;margin-right:5px;line-height:20px;}
#map-help dl.toolbar dt:before{margin-top:0.2em !important;float:left;}
#map-help dt,#map-help dd{line-height:20px;}
#example-data{height:220px;}
#example-data img{vertical-align:middle;margin-right:20px;}
#country-codes{position:relative;width:200px;}
#country-codes .mapper-loading-message{left:5%;}
</style>
<script async src="../public/javascript/jquery.waypoints.min.js"></script>
<script>
$(function () {
  $('#map-help').waypoint(function() {
    var data = {},
        elem = $('#country-codes').css("width", "100%");

    if (SimpleMappr.getParameterByName("locale")) { data.locale = SimpleMappr.getParameterByName("locale"); }
    if($('#country-codes').html().length === 0) { SimpleMappr.loadCodes(elem, data); }
  });
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

    <dt class="sprites-before toolsUndo"><?php echo _("Undo"); ?>:</dt>
    <dd><?php echo _("undo the last executed action"); ?></dd>

    <dt class="sprites-before toolsRedo"><?php echo _("Redo"); ?>:</dt>
    <dd><?php echo _("redo the last executed action"); ?></dd>

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

    <dt><?php echo _("oceans (greyscale)"); ?>:</dt>
    <dd><?php echo _("select this checkbox to overlay oceans as greyscale polygons"); ?></dd>

    <dt><?php echo _("relief"); ?>:</dt>
    <dd><?php echo _("select this checkbox to render a color, shaded relief layer"); ?></dd>

    <dt><?php echo _("relief (greyscale)"); ?>:</dt>
    <dd><?php echo _("select this checkbox to render a greyscale, shaded relief layer"); ?></dd>

    <dt><?php echo _("biodiv. hotspots"); ?>:</dt>
    <dd><?php echo _("select this checkbox to render biodiversity hotspots known to Conservation International"); ?></dd>
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
    <dd><?php echo _("draw a graticule (grid) layer on the map using either fixed, 1, 5, or 10 degree spacing"); ?></dd>

    <dt><?php echo _("Line Thickness"); ?>:</dt>
    <dd><?php echo _("use the slider to increase or decrease line thickness of the base and State/Provinces layers")?>
  </dl>

  <h3><?php echo _("Projection"); ?></h3>
  <dl class="ui-helper-clearfix">
    <dt><?php echo _("projection"); ?>:</dt>
    <dd><?php echo _("choose among several projections. [Hint: first use zoom while on the base geographic projection for best effects]"); ?></dd>

    <dt><?php echo _("longitude of natural origin"); ?>:</dt>
    <dd><?php echo _("type the longitude for the central meridian"); ?></dd>
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
    <p><?php echo _("Use the Point Data tab to paste coordinates as latitude, longitude either in decimal degrees or as DD°MM'SS\" on separate lines and select the marker shape, size, and color."); ?></p>

    <dl class="ui-helper-clearfix">
      <dt><?php echo _("Coordinate format"); ?>:</dt>
      <dd><?php echo _("in western hemisphere above equator 45.55, -120.25 (or 45° 33'N, 120° 15'W); in western hemisphere below equator -15.66, -65.10; eastern hemisphere above equator 64.82, 75.1"); ?></dd>
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

    <div id="country-codes"></div>

</div>