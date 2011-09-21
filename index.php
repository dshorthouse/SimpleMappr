<?php

require_once('conf/conf.php');

if(isset($_GET['map'])) {
  require_once('includes/mapprservice.embed.class.php');
  $mappr_embed = new MAPPREMBED();
  $mappr_embed->set_shape_path(MAPPR_DIRECTORY . "/maps")
              ->set_symbols_path(MAPPR_DIRECTORY . "/config/symbols")
              ->set_font_file(MAPPR_DIRECTORY . "/config/fonts.list")
              ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
              ->set_tmp_url("/tmp");

  $mappr_embed->get_request()
              ->execute()
              ->get_output();
  exit();
}

require_once('includes/mapprservice.header.class.php');
require_once('includes/mapprservice.class.php');
require_once('includes/jsmin.php');

session_start();

$header = new HEADER;

$host = explode(".", $_SERVER['HTTP_HOST']);

if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
  header('Location: http://www.simplemappr.net/');
}

if(isset($_COOKIE["simplemappr"])) {
    $_SESSION["simplemappr"] = (array)json_decode(stripslashes($_COOKIE["simplemappr"]));
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="description" content="A publication-quality, point map application." />
<meta name="keywords" content="publication,map" />
<meta name="author" content="David P. Shorthouse" />
<title>SimpleMappr</title>
<link type="image/x-icon" href="favicon.ico" rel="SHORTCUT ICON" />
<?php
$header->getCSSHeader();
$header->getJSHeader();
?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery.extend(Mappr.settings, { "baseUrl": "http://<?php echo $_SERVER['HTTP_HOST']; ?>" });
//--><!]]>
</script>
</head>

<body>
<h1 id="site-title"><img src="images/logo.png" alt="SimpleMappr" /><span>SimpleMappr</span></h1>
<div id="site-tagline">point maps for publication</div>
<?php if(isset($_SESSION['simplemappr'])): ?>
<div id="site-logout">Welcome back <?php echo $_SESSION['simplemappr']['username']; ?> <span><a class="sprites site-logout" href="/usermaps/?action=logout">Log Out</a></span></div>
<?php else: ?>
<div id="site-logout"><span><a class="sprites site-login" href="#" onclick="javascript:Mappr.tabSelector(3);return false;">Log In</a></span></div>
<?php endif; ?>
<div id="wrapper">

    <div id="initial-message">Building page...</div>

    <div id="tabs">

        <ul class="navigation">
            <li><a href="#map-preview">Preview</a></li>
            <li><a href="#map-points">Point Data</a></li>
            <li><a href="#map-regions">Regions</a></li>
<!--  
// Freehand Drawing commented out until projection issues fully resolved
// Once resolved, commenting on tab below must also be removed
            <li><a href="#map-freehand">Freehand</a></li>
-->
            <li><a href="#map-mymaps" class="sprites map-mymaps">
                <?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
                    All Maps
                <?php else: ?>
                    My Maps
                <?php endif; ?>
                </a></li>
            <?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
                <li><a href="#map-users" class="sprites map-users">Users</a></li>
            <?php endif; ?>
            <li class="map-extras"><a href="tabs/help.php" class="sprites map-myhelp">Help</a></li>
            <li class="map-extras"><a href="#map-about">About</a></li>
            <li class="map-extras"><a href="tabs/feedback.php">Feedback</a></li>
            <li class="map-extras"><a href="tabs/api.php">API</a></li>
        </ul>

    <form id="form-mapper" action="application/" method="post" autocomplete = "off">  


        <!-- about tab -->
        <div id="map-about">
            <div id="general-about" class="panel">
            <p>Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result.</p>
            </div>

            <h2>Citing</h2>
            <p>Shorthouse, David P. 2010. SimpleMappr, a web-enabled tool to produce publication-quality point maps. Retrieved from http://www.simplemappr.net. Accessed <?php echo date("Y-m-d"); ?>.</p>

            <h2>Recent Updates</h2>
            <p class="citation"><strong>September 21, 2011</strong> Added graticule options. Fixed production of KML files. Cleaned presentation of download options.</p>
            <p class="citation"><strong>August 2, 2011</strong> Refined error-handling with coordinate recognition.</p>
            <p class="citation"><strong>July 5, 2011</strong> Added the ability to filter your My Maps list by title.</p>
            <p class="citation"><strong>July 4, 2011</strong> The fill bucket in the map toolbar now produces a colour selector and regions may be immediately filled by either clicking or click-dragging on the map. Repeating this process adds another layer to the Regions tab. Clear buttons were added to each layer in the Point Data and Regions tabs.</p>
            <p class="citation"><strong>July 3, 2011</strong> State/Province line artifacts are not shown when the map is reprojected.</p>
            <p class="citation"><strong>June 28, 2011</strong> Additional layers on the Point Data or Regions tabs may be removed.</p>
            <p class="citation"><strong>June 27, 2011</strong> An ISO Country codes and regions code table was added to the Help tab.</p>
            <p class="citation"><strong>June 26, 2011</strong> File names may be specified when downloading maps.</p>

            <h2>In the Wild</h2>
            <p class="citation">Carr, Christina May. 2011. Polychaete diversity and distribution patterns in Canadian marine waters. <em>Marine Biodiversity</em> Online first, doi:<a href="http://dx.doi.org/10.1007/s12526-011-0095-y">10.1007/s12526-011-0095-y</a></p>
            <p class="citation">Carr, C.M., Hardy, S.M., Brown, T.M., Macdonald, T.A., Hebert, P.D.N. 2011. A Tri-Oceanic Perspective: DNA Barcoding Reveals Geographic Structure and Cryptic Diversity in Canadian Polychaetes. <em>PLoS ONE</em> 6(7): e22232. doi:<a href="http://dx.doi.org/10.1371/journal.pone.0022232">10.1371/journal.pone.0022232</a></p>
            <p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">permalink</a>)</p>

            <h2>Code</h2>
            <p>The code behind SimpleMappr may be obtained at <a href="https://github.com/dshorthouse/SimpleMappr">https://github.com/dshorthouse/SimpleMappr</a>.</p>

            <h2>History</h2>
            <p>The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History.</p>

            <h2>Acknowledgments</h2>
            <p>Underlying ArcView shapefiles were obtained from Natural Earth, <a href="http://www.naturalearthdata.com/" target="_blank">http://www.naturalearthdata.com/</a> and the mapping software used is MapServer, <a href="http://mapserver.org" target="_blank">http://mapserver.org</a> via PHP MapScript.</p>

        </div>

    <!-- multipoint tab -->
    <div id="map-points">
        <div id="general-points" class="panel">
        <p>Type geographic coordinates on separate lines in decimal degrees as latitude longitude (separated by a space, comma, or semicolon) <a href="#" onclick="javascript:Mappr.showExamples(); return false;" class="sprites help">examples</a></p>
        </div>

        <div id="fieldSetsPoints" class="fieldSets">

<?php
    //marker sizes and shapes
    $marker_size  = '<option value="">--select--</option>';
    $marker_size .= '<option value="6">6pt</option>';
    $marker_size .= '<option value="8">8pt</option>';
    $marker_size .= '<option value="10" selected="selected">10pt</option>';
    $marker_size .= '<option value="12">12pt</option>';
    $marker_size .= '<option value="14">14pt</option>';
    $marker_size .= '<option value="16">16pt</option>';

    $marker_shape  = '<option value="">--select--</option>';
    $marker_shape .= '<option value="plus">plus</option>';
    $marker_shape .= '<option value="cross">cross</option>';
    $marker_shape .= '<optgroup label="solid">';
    $marker_shape .= '<option value="circle" selected="selected">circle (s)</option>';
    $marker_shape .= '<option value="star">star (s)</option>';
    $marker_shape .= '<option value="square">square (s)</option>';
    $marker_shape .= '<option value="triangle">triangle (s)</option>';
    $marker_shape .= '</optgroup>';
    $marker_shape .= '<optgroup label="open">';
    $marker_shape .= '<option value="opencircle">circle (o)</option>';
    $marker_shape .= '<option value="openstar">star (o)</option>';
    $marker_shape .= '<option value="opensquare">square (o)</option>';
    $marker_shape .= '<option value="opentriangle">triangle (o)</option>';
    $marker_shape .= '</optgroup>';

    for($j=0;$j<=NUMTEXTAREA-1;$j++) {
      
      echo '<div class="form-item fieldset-points">';

      echo '<button class="sprites removemore negative" data-type="coords">Remove</button>';
    
      echo '<h3><a href="#">Layer '.($j+1).'</a></h3>' . "\n";
      echo '<div>' . "\n";
      echo '<div class="fieldset-taxon">' . "\n";
      echo '<span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="coords['.$j.'][title]" />' . "\n";
      echo '</div>' . "\n";
      echo '<div class="resizable-textarea">' . "\n";
      echo '<span>' . "\n";
      echo '<textarea class="resizable m-mapCoord" rows="5" cols="60" name="coords['.$j.'][data]"></textarea>' . "\n";
      echo '</span>' . "\n";
      echo '</div>' . "\n";

      echo '<div class="fieldset-extras">' . "\n";
      echo '<span class="fieldset-title">Shape:</span> <select class="m-mapShape" name="coords['.$j.'][shape]">'.$marker_shape.'</select> <span class="fieldset-title">Size:</span> <select class="m-mapSize" name="coords['.$j.'][size]">'.$marker_size.'</select>' . "\n";
      echo '<span class="fieldset-title">Color:</span> <input class="colorPicker" type="text" size="12" maxlength="11" name="coords['.$j.'][color]" value="0 0 0" />' . "\n";
      echo '</div>' . "\n";
      echo '<button class="sprites clear clearself negative">Clear</button>' . "\n";
      echo '</div>' . "\n";
    
      echo '</div>' . "\n";
    }

?>

        </div>

        <div class="addFieldset">
            <button class="sprites addmore positive" data-type="coords">Add a layer</button>
        </div>

        <div class="submit">
            <button class="sprites submitForm positive">Preview</button>
            <button class="sprites clear clearLayers negative">Clear all</button>
        </div>
        
    <!-- close multipoints tab -->
    </div>

    <!-- shaded regions tab -->
    <div id="map-regions">
        <div id="regions-introduction" class="panel">
<?php $tabIndex = (isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1) ? 5 : 4; ?>
            <p>Type countries <em>e.g.</em> Mexico, Venezuela AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code <em>e.g.</em>USA[VA], CAN[AB ON]. <a href="#" onclick="javascript:Mappr.tabSelector(<?php echo $tabIndex; ?>);return false;" class="sprites help">codes</a></p> 
        </div>

        <div id="fieldSetsRegions" class="fieldSets">
<?php
        for($j=0;$j<=NUMTEXTAREA-1;$j++) {
          
          echo '<div class="form-item fieldset-regions">';

          echo '<button class="sprites removemore negative" data-type="regions">Remove</button>';

          echo '<h3><a href="#">Region '.($j+1).'</a></h3>' . "\n";
          echo '<div>' . "\n";
          echo '<div class="fieldset-taxon">' . "\n";
          echo '<span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="regions['.$j.'][title]" />' . "\n";
          echo '</div>' . "\n";
          echo '<div class="resizable-textarea">' . "\n";
          echo '<span>' . "\n";
          echo '<textarea class="resizable m-mapCoord" rows="5" cols="60" name="regions['.$j.'][data]"></textarea>' . "\n";
          echo '</span>' . "\n";
          echo '</div>' . "\n";
        
          echo '<div class="fieldset-extras">' . "\n";
          echo '<span class="fieldset-title">Color:</span> <input type="text" class="colorPicker" size="12" maxlength="11" name="regions['.$j.'][color]" value="150 150 150" />' . "\n";
          echo '</div>' . "\n";
          echo '<button class="sprites clear clearself negative">Clear</button>' . "\n";
          echo '</div>' . "\n";
        
          echo '</div>' . "\n";

        }
?>
        </div>
        
        <div class="addFieldset">
            <button class="sprites addmore positive" data-type="regions">Add a region</button>
        </div>

        <div class="submit">
            <button class="sprites submitForm positive">Preview</button>
            <button class="sprites clear clearRegions negative">Clear all</button>
        </div>

    </div>
    <!-- close shaded regions tab --> 

    <!-- shaded freehand tab -->
<!--
// Commented out for now until projection issues fully resolved with freehand drawing

    <div id="map-freehand">
            <div id="freehand-introduction" class="panel">
                <p>Type well-known text (<em>e.g.</em>  POLYGON((-103 54,-111 51,-100 49,-103 54)))</p> 
            </div>

            <div id="fieldSetsFreehands" class="fieldSets">
    <?php
            for($j=0;$j<=NUMTEXTAREA-1;$j++) {

              echo '<div class="form-item fieldset-freehands">';

              echo '<button class="sprites removemore negative" data-type="freehands">Remove</button>';

              echo '<h3><a href="#">Freehand '.($j+1).'</a></h3>' . "\n";
              echo '<div>' . "\n";
              echo '<div class="fieldset-taxon">' . "\n";
              echo '<span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="freehand['.$j.'][title]" />' . "\n";
              echo '</div>' . "\n";
              echo '<div class="resizable-textarea">' . "\n";
              echo '<span>' . "\n";
              echo '<textarea class="resizable m-mapCoord" rows="5" cols="60" name="freehand['.$j.'][data]"></textarea>' . "\n";
              echo '</span>' . "\n";
              echo '</div>' . "\n";

              echo '<div class="fieldset-extras">' . "\n";
              echo '<span class="fieldset-title">Color:</span> <input type="text"  class="colorPicker" size="12" maxlength="11" name="freehand['.$j.'][color]" value="150 150 150" />' . "\n";
              echo '</div>' . "\n";
              echo '<button class="sprites clear clearself negative">Clear</button>' . "\n";
              echo '</div>' . "\n";

              echo '</div>' . "\n";

            }
    ?>
            </div>

            <div class="addFieldset">
                <button class="sprites addmore positive" data-type="freehands">Add a region</button>
            </div>

            <div class="submit">
                <button class="sprites submitForm positive">Preview</button>
                <button class="sprites clear clearFreehand negative">Clear all</button>
            </div>

    </div>
-->
    <!-- close freehand tab -->

    <!-- map preview tab -->
    <div id="map-preview">
        <div id="mapWrapper">
            <div id="actionsBar" class="ui-widget-header ui-corner-all">
                      <ul class="dropdown">
                      <li><a href="#" class="sprites toolsZoomIn tooltip" title="zoom in"></a></li>
                      <li><a href="#" class="sprites toolsZoomOut tooltip" title="zoom out"></a></li>
                      <li><a href="#" class="sprites rotateAnticlockwise tooltip" title="rotate counterclockwise"></a>
                        <ul class="sub_menu">
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="-5">5<sup>o</sup></a></li>
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="-10">10<sup>o</sup></a></li>
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="-15">15<sup>o</sup></a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="sprites rotateClockwise tooltip" title="rotate clockwise"></a>
                        <ul class="sub_menu">
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="5">5<sup>o</sup></a></li>
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="10">10<sup>o</sup></a></li>
                          <li class="ui-state-default"><a href="#" class="sprites toolsRotate" data-rotate="15">15<sup>o</sup></a></li>
                        </ul>
                      </li>
                      <li><a href="#" class="sprites toolsCrop tooltip" title="crop"></a></li>
                      <li><a href="#" class="sprites toolsQuery tooltip" title="fill regions"></a></li>
<!--
// Commented out for now until projection issues fully resolve with freehand
                      <li><a href="#" class="sprites toolsDraw tooltip" title="draw shape"></a></li>
-->
                      <li><a href="#" class="sprites toolsRefresh tooltip" title="refresh"></a></li>
                      <li><a href="#" class="sprites toolsRebuild tooltip" title="rebuild"></a></li>
                    </ul>
                    
                    <h3 id="mapTitle"></h3>
                    
                    <div id="map-saveDialog">
                        <?php if(isset($_SESSION['simplemappr'])): ?>
                        <span><a class="sprites map-saveItem map-save tooltip" href="#" title="save">Save</a></span>
                        <span><a class="sprites map-saveItem map-embed tooltip" href="#" title="embed" data-mid="">Embed</a></span>
                        <?php endif; ?>
                        <span><a class="sprites map-saveItem map-download tooltip" href="#" title="download">Download</a></span>
                    </div>
                    
            </div>
            <div class="clear"></div>
            
            <div id="map">
                <div id="mapImage">
                    <div id="mapControlsTransparency"></div>
                    <div id="mapControls">
                          <a href="#" class="sprites controls arrows up" data-pan="up"></a>
                          <a href="#" class="sprites controls arrows right" data-pan="right"></a>
                          <a href="#" class="sprites controls arrows down" data-pan="down"></a>
                          <a href="#" class="sprites controls arrows left" data-pan="left"></a>
                    </div>
                    <div id="badRecordsWarning"><a href="#" class="sprites toolsBadRecords">Records Out of Range</a></div>
                    <div id="mapOutput">
                        <img id="mapOutputImage" src="images/basemap.png" alt="" />
                    </div>
                </div>
                <div id="mapScale"></div>
            </div>
            
            <div id="mapTools">

              <ul>
                <li><a href="#mapOptions">Settings</a></li>
                <li><a href="#mapLegend">Legend</a></li>
              </ul>

              <div id="mapLegend"><p><em>legend will appear here</em></p></div>

              <div id="mapOptions">
                <h2>Layers</h2>
                <ul>
                    <li><input type="checkbox" id="stateprovince" class="layeropt" name="layers[stateprovinces]" /> State/Province borders</li>
                    <li><input type="checkbox" id="placenames" class="layeropt" name="layers[placenames]" /> place names</li>
                    <li><input type="checkbox" id="physicalLabels" class="layeropt" name="layers[physicalLabels]" /> physical labels</li>
                    <li><input type="checkbox" id="marineLabels" class="layeropt" name="layers[marineLabels]" /> marine labels</li>
                    <li><input type="checkbox" id="lakesOutline" class="layeropt" name="layers[lakesOutline]" /> lakes (outline)</li>
                    <li><input type="checkbox" id="lakes" class="layeropt" name="layers[lakes]" /> lakes (filled)</li>
                    <li><input type="checkbox" id="rivers" class="layeropt" name="layers[rivers]" /> rivers</li>
                    <li><input type="checkbox" id="relief" class="layeropt" name="layers[relief]" /> shaded relief</li>
<!-- 
// The following selection is based on a GeoTiff created by David P. Shorthouse and is not available at NaturalEarth
-->
                    <li><input type="checkbox" id="reliefgrey" class="layeropt" name="layers[reliefgrey]" /> shaded relief (greyscale)</li>
                </ul>
                <h2>Options</h2>
                <ul>
                    <li><input type="checkbox" id="scalebar"  class="layeropt" name="options[scalebar]" /> scalebar</li>
<!--                    <li><input type="checkbox" id="arrow"  class="layeropt" name="options[arrow]" /> north arrow</li>
-->
                    <li><input type="checkbox" id="graticules"  class="layeropt" name="layers[grid]" /> graticules
                      <div id="graticules-selection">
                        <input type="radio" id="gridspace" class="gridopt" name="gridspace" value="" checked="checked" /> fixed
                        <input type="radio" id="gridspace-5" class="gridopt" name="gridspace" value="5" /> 5<sup>o</sup>
                        <input type="radio" id="gridspace-10" class="gridopt" name="gridspace" value="10" /> 10<sup>o</sup>
                      </div>
                    </li>
                </ul>
                <h2>Projection*</h2>
                <ul>
                  <li>
                        <select id="projection" name="projection">
                    <?php
                      foreach(MAPPR::$accepted_projections as $key => $value) {
                        $selected = ($value['name'] == 'Geographic') ? ' selected="selected"': '';
                        echo '<option value="'.$key.'"'.$selected.'>'.$value['name'].'</option>' . "\n";
                      }
                    ?>
                        </select>
                  </li>
                </ul>
                        <p>*zoom prior to setting projection</p>
              </div> <!-- /mapOptions -->
                
            </div> <!-- /mapTools -->
            
        </div>
    </div>
    
    <!-- my maps tab -->
    <div id="map-mymaps">
        <?php if(!isset($_SESSION['simplemappr'])): ?>
            <div class="panel">
                <p>Save and reload your map data or create a generic template.</p> 
            </div>
           <iframe src="http://simplemappr.rpxnow.com/openid/embed?token_url=http%3A%2F%2F<?php echo $_SERVER['HTTP_HOST']; ?>%2Fusermaps%2Frpx.php"  scrolling="no" style="width:400px;height:240px;border:none"></iframe> 
        <?php else: ?>
            <div id="usermaps"></div>
        <?php endif; ?>
    </div>
    
    <!-- users tab -->
    <?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
        <div id="map-users">
            <div id="userdata"></div>
        </div>
    <?php endif; ?>

    <!-- hidden form elements for map preview -->
    <input type="hidden" id="download" name="download" />
    <input type="hidden" id="output" name="output" />
    <input type="hidden" id="download_legend" name="download_legend" />
    
    <!-- download token -->
    <input type="hidden" id="download_token" name="download_token" />
    
    <!-- bounding box of map image in whatever projection map is in -->
    <input type="hidden" id="bbox_map" name="bbox_map" />
    
    <!-- projection of map image -->
    <input type="hidden" id="projection_map" name="projection_map" />

    <!-- coordinates of bounding box in pixels where top left is (x,y) and bottom right is (x2,y2)-->
    <input type="hidden" id="bbox_rubberband" name="bbox_rubberband" />
    
    <!-- coordinates of bounding box for shading selected regions -->
    <input type="hidden" id="bbox_query" name="bbox_query" />
    
    <input type="hidden" id="pan" name="pan" />
    <input type="hidden" id="zoom_out" name="zoom_out" />
    <input type="hidden" id="crop" name="crop" />
    <input type="hidden" id="rotation" name="rotation" />
    
    <!-- selected tab -->
    <input type="hidden" id="selectedtab" name="selectedtab" />
    
    <!-- put modal form elements back into flow of DOM -->
    <input type="hidden" name="save[title]" />
    <input type="hidden" name="file_name" />
    <input type="hidden" name="download_factor" />
    <input type="hidden" name="download_filetype" />
    <input type="hidden" name="grid_space" />
    <input type="hidden" name="options[border]" />
    <input type="hidden" name="options[legend]" />
    
    <div id="badRecordsViewer" title="Records out of range">
        <div id="badRecords"></div>
    </div>
    
    <div id="mapSave" title="Save Map">
        <div class="fieldset-taxon">
        <span class="fieldset-title">Title<span class="required">*</span>:</span> <input type="text" class="m-mapSaveTitle" size="30" maxlength="30" />
        </div>
    </div>

      <div id="mapExport" title="Download Map">
        <div class="download-dialog">
        <div id="mapCropMessage" class="sprites">map will be cropped</div>

        <p>
          <label for="file-name">File name:</label>
          <input type="text" id="file-name" maxlength="30" size="30" />
        </p>

        <fieldset>
          <legend>Scale</legend>
        <?php
          $file_sizes = array(3,4,5);
          foreach($file_sizes as $size) {
            $checked = ($size == 3) ? " checked=\"checked\"" : "";
            echo "<input type=\"radio\" id=\"download-factor-".$size."\" name=\"download-factor\" value=\"".$size."\"".$checked." />";
            echo "<label for=\"download-factor-".$size."\">".$size."X</label>";
          }
        ?>
        </fieldset>

        <fieldset>
          <legend>File type</legend>
        <?php
          $file_types = array('svg', 'png', 'tif', 'eps', 'kml');
          foreach($file_types as $type) {
            $checked = ($type == "svg") ? " checked=\"checked\"": "";
            $asterisk = ($type == "svg") ? "*" : "";
            echo "<input type=\"radio\" id=\"download-".$type."\" name=\"download-filetype\" value=\"".$type."\"".$checked." />";
            echo "<label for=\"download-".$type."\">".$type.$asterisk."</label>";
          }
        ?>
        </fieldset>

        <fieldset>
          <legend>Options</legend>
            <input type="checkbox" id="border" />
            <label for="border">include border</label>
            <input type="checkbox" id="legend" />
            <label for="legend">include legend</label>
        </fieldset>

        <p>*svg download does not include scalebar, legend, or relief layers</p>
        </div>
        
        <div class="download-message">Building file for download...</div>
      </div>

    </form>

    <!-- close tabs wrapper -->
    </div>

</div>
<?php
$header->getAnalytics();
?>
</body>
</html>
