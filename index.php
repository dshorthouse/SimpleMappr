<?php

require_once('config/conf.php');

if(isset($_GET['map'])) {
  require_once('lib/mapprservice.embed.class.php');
  $mappr_embed = new MAPPREMBED();
  $mappr_embed->set_shape_path(MAPPR_DIRECTORY . "/lib/mapserver/maps")
              ->set_font_file(MAPPR_DIRECTORY . "/lib/mapserver/fonts/fonts.list")
              ->set_tmp_path(MAPPR_DIRECTORY . "/tmp/")
              ->set_tmp_url("/tmp");

  $mappr_embed->get_request()
              ->execute()
              ->get_output();
  exit();
}

require_once('lib/mapprservice.header.class.php');
require_once('lib/mapprservice.class.php');

session_start();

$header = new HEADER;
$host = explode(".", $_SERVER['HTTP_HOST']);
if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) { header('Location: http://www.simplemappr.net/'); }
if(isset($_COOKIE["simplemappr"])) { $_SESSION["simplemappr"] = (array)json_decode(stripslashes($_COOKIE["simplemappr"])); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="description" content="A publication-quality, point map application." />
<meta name="keywords" content="publication,map" />
<meta name="author" content="David P. Shorthouse" />
<title>SimpleMappr</title>
<link type="image/x-icon" href="favicon.ico" rel="SHORTCUT ICON" />
<?php $header->getCSSHeader(); ?>
</head>
<?php flush(); ?>
<body>
<h1 id="site-title"><img src="public/images/logo.png" alt="SimpleMappr" width="327" height="40" /><span>SimpleMappr</span></h1>
<div id="site-tagline">point maps for publication</div>
<?php if(isset($_SESSION['simplemappr'])): ?>
<div id="site-logout"><?php echo $_SESSION['simplemappr']['username']; ?> <span><a class="sprites site-logout" href="/logout/">Sign Out</a></span></div>
<?php else: ?>
<div id="site-logout"><span><a class="sprites site-login" href="#" onclick="javascript:Mappr.tabSelector(3);return false;">Sign In</a></span></div>
<?php endif; ?>
<div id="wrapper">
<div id="initial-message" class="ui-corner-all ui-widget-content">Building page...</div>
<div id="tabs">
<ul class="navigation">
<li><a href="#map-preview">Preview</a></li>
<li><a href="#map-points">Point Data</a></li>
<li><a href="#map-regions">Regions</a></li>
<li><a href="#map-mymaps" class="sprites map-mymaps tooltip" title="Saved Maps ctrl+l" onclick="javascript: Mappr.analytics('/mymaps'); "><?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>All Maps<?php else: ?>My Maps<?php endif; ?></a></li>
<?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
<li><a href="#map-users" class="sprites map-users">Users</a></li>
<?php endif; ?>
<li class="map-extras"><a href="tabs/help.php" class="sprites map-myhelp" onclick="javascript: Mappr.analytics('/help'); ">Help</a></li>
<li class="map-extras"><a href="tabs/about.php" onclick="javascript: Mappr.analytics('/about'); ">About</a></li>
<li class="map-extras"><a href="tabs/feedback.php" onclick="javascript: Mappr.analytics('/feedback'); ">Feedback</a></li>
<li class="map-extras"><a href="tabs/api.php" onclick="javascript: Mappr.analytics('/api'); ">API</a></li>
</ul>
<form id="form-mapper" action="application/" method="post" autocomplete = "off">  

<!-- multipoint tab -->
<div id="map-points">
<div id="general-points" class="panel ui-corner-all">
<p>Type geographic coordinates on separate lines in decimal degrees as latitude longitude (separated by a space, comma, or semicolon) <a href="#" onclick="javascript:Mappr.showExamples(); return false;" class="sprites help">examples</a></p>
</div>
<div id="fieldSetsPoints" class="fieldSets">
<?php echo partial_layers(); ?>
</div>
<div class="addFieldset"><button class="sprites addmore positive" data-type="coords">Add a layer</button></div>
<div class="submit"><button class="sprites submitForm positive">Preview</button><button class="sprites clear clearLayers negative">Clear all</button></div>
<!-- close multipoints tab -->
</div>
<!-- shaded regions tab -->
<div id="map-regions">
<div id="regions-introduction" class="panel ui-corner-all">
<?php $tabIndex = (isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1) ? 5 : 4; ?>
<p>Type countries <em>e.g.</em> Mexico, Venezuela AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code <em>e.g.</em>USA[VA], CAN[AB ON]. <a href="#" onclick="javascript:Mappr.tabSelector(<?php echo $tabIndex; ?>);return false;" class="sprites help">codes</a></p>
</div>
<div id="fieldSetsRegions" class="fieldSets">
<?php echo partial_regions(); ?>
</div>
<div class="addFieldset"><button class="sprites addmore positive" data-type="regions">Add a region</button></div>
<div class="submit"><button class="sprites submitForm positive">Preview</button><button class="sprites clear clearRegions negative">Clear all</button></div>
</div>
<!-- close shaded regions tab --> 
<!-- map preview tab -->
<div id="map-preview">
<div id="mapWrapper">
<div id="actionsBar" class="ui-widget-header ui-corner-all ui-helper-clearfix">
<ul class="dropdown">
<li><a href="#" class="sprites toolsZoomIn tooltip" title="zoom in ctrl+"></a></li>
<li><a href="#" class="sprites toolsZoomOut tooltip" title="zoom out ctrl-"></a></li>
<li><a href="#" class="sprites toolsCrop tooltip" title="crop ctrl+x"></a></li>
<li><a href="#" class="sprites toolsQuery tooltip" title="fill regions"></a></li>
<li><a href="#" class="sprites toolsRefresh tooltip" title="refresh ctrl+r"></a></li>
<li><a href="#" class="sprites toolsRebuild tooltip" title="rebuild ctrl+n"></a></li>
</ul>
<h3 id="mapTitle"></h3>
<div id="map-saveDialog">
<?php if(isset($_SESSION['simplemappr'])): ?>
<span><a class="sprites map-saveItem map-save tooltip" href="#" title="save ctrl+s">Save</a></span>
<span><a class="sprites map-saveItem map-embed tooltip" href="#" title="embed" data-mid="">Embed</a></span>
<?php endif; ?>
<span><a class="sprites map-saveItem map-download tooltip" href="#" title="download ctrl+d">Download</a></span>
</div>
</div>
<div id="map">
<div id="mapImage">
<div id="mapControlsTransparency"></div>
<div id="mapControls">
<div class="viewport">
<ul class="overview">
<?php echo rotation_values(); ?>
</ul>
</div>
<div class="dot"></div>
<div class="overlay">
<a href="#" class="sprites controls tooltip arrows up" data-pan="up" title="pan up"></a>
<a href="#" class="sprites controls tooltip arrows right" data-pan="right" title="pan right"></a>
<a href="#" class="sprites controls tooltip arrows down" data-pan="down" title="pan down"></a>
<a href="#" class="sprites controls tooltip arrows left" data-pan="left" title="pan left"></a>
</div>
<div class="thumb ui-corner-all ui-widget-header"></div>
</div>
<div id="badRecordsWarning"><a href="#" class="sprites toolsBadRecords">Records Out of Range</a></div>
<div id="mapOutput"><span class="mapper-loading-message ui-corner-all ui-widget-content">Building preview...</span></div>
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
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="stateprovince" class="layeropt" name="layers[stateprovinces]" /> State/Provinces</li>
<li><input type="checkbox" id="lakesOutline" class="layeropt" name="layers[lakesOutline]" /> lakes (outline)</li>
<li><input type="checkbox" id="lakes" class="layeropt" name="layers[lakes]" /> lakes (greyscale)</li>
<li><input type="checkbox" id="rivers" class="layeropt" name="layers[rivers]" /> rivers</li>
<li><input type="checkbox" id="relief" class="layeropt" name="layers[relief]" /> relief</li>
<li><input type="checkbox" id="reliefgrey" class="layeropt" name="layers[reliefgrey]" /> relief (greyscale)</li>
</ul>
<h2>Labels</h2>
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="countrynames" class="layeropt" name="layers[countrynames]" /> Countries</li>
<li><input type="checkbox" id="stateprovincenames" class="layeropt" name="layers[stateprovnames]" /> State/Provinces</li>
<li><input type="checkbox" id="lakenames" class="layeropt" name="layers[lakenames]" /> lakes</li>
<li><input type="checkbox" id="rivernames" class="layeropt" name="layers[rivernames]" /> rivers</li>
<li><input type="checkbox" id="placenames" class="layeropt" name="layers[placenames]" /> places</li>
<li><input type="checkbox" id="physicalLabels" class="layeropt" name="layers[physicalLabels]" /> physical</li>
<li><input type="checkbox" id="marineLabels" class="layeropt" name="layers[marineLabels]" /> marine</li>
</ul>
<h2>Options</h2>
<ul>
<li><input type="checkbox" id="graticules"  class="layeropt" name="layers[grid]" /> graticules (grid)
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
<div class="panel ui-corner-all">
<p>Save and reload your map data or create a generic template.</p> 
</div>
<div id="janrainEngageEmbed"></div>
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
<div id="badRecordsViewer" title="Records out of range"><div id="badRecords"></div></div>
<div id="mapSave" title="Save">
<p>
<label for="m-mapSaveTitle">Title<span class="required">*</span></label>
<input type="text" id="m-mapSaveTitle" class="m-mapSaveTitle" size="30" maxlength="30" />
</p>
</div>
<div id="mapExport" title="Download">
<div class="download-dialog">
<p id="mapCropMessage" class="sprites">map will be cropped</p>
<p>
<label for="file-name">File name:</label>
<input type="text" id="file-name" maxlength="30" size="30" />
</p>
<fieldset>
<legend>Scale</legend>
<?php echo partial_scales(); ?>
</fieldset>
<fieldset>
<legend>File type</legend>
<?php echo partial_filetypes(); ?>
</fieldset>
<fieldset>
<legend>Options</legend>
<input type="checkbox" id="border" />
<label for="border">include border</label>
<input type="checkbox" id="legend" disabled="disabled" />
<label for="legend">embed legend</label>
<input type="checkbox" id="scalebar" disabled="disabled" />
<label for="scalebar">embed scalebar</label>
</fieldset>
<p>*svg does not include scalebar, legend, or relief layers</p>
</div>
<div class="download-message">Building file for download...</div>
</div>
<input type="hidden" name="download" id="download"/>
<input type="hidden" name="output" id="output" />
<input type="hidden" name="download_token" id="download_token"/>
<input type="hidden" name="bbox_map" id="bbox_map" />
<input type="hidden" name="projection_map" id="projection_map" />
<input type="hidden" name="bbox_rubberband" id="bbox_rubberband" />
<input type="hidden" name="bbox_query" id="bbox_query" />
<input type="hidden" name="pan" id="pan" />
<input type="hidden" name="zoom_out" id="zoom_out" />
<input type="hidden" name="crop" id="crop" />
<input type="hidden" name="rotation" id="rotation" />
<input type="hidden" name="selectedtab" id="selectedtab" />
<input type="hidden" name="save[title]" />
<input type="hidden" name="file_name" />
<input type="hidden" name="download_factor" />
<input type="hidden" name="download_filetype" />
<input type="hidden" name="grid_space" />
<input type="hidden" name="options[border]" />
<input type="hidden" name="options[legend]" />
<input type="hidden" name="options[scalebar]" />
<input type="hidden" id="rendered_bbox" value="" />
<input type="hidden" id="rendered_rotation" value="" />
<input type="hidden" id="rendered_projection" value="" />
<input type="hidden" id="legend_url" value="" />
<input type="hidden" id="scalebar_url" value="" />
<input type="hidden" id="bad_points" value="" />
</form>
<!-- close tabs wrapper -->
</div>
</div>
<?php $header->getJSHeader();?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery.extend(Mappr.settings, { "baseUrl": "http://<?php echo $_SERVER['HTTP_HOST']; ?>", "active" : <?php echo (isset($_SESSION['simplemappr'])) ? "\"true\"" : "\"false\""; ?> });
//--><!]]>
</script>
<?php $header->getAnalytics(); ?>
</body>
</html>
<?php

function partial_layers() {
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

  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    
    $output .= "<div class=\"form-item fieldset-points\">";

    $output .= "<button class=\"sprites removemore negative\" data-type=\"coords\">Remove</button>";
  
    $output .= "<h3><a href=\"#\">Layer ".($i+1)."</a></h3>" . "\n";
    $output .= "<div>" . "\n";
    $output .= "<div class=\"fieldset-taxon\">" . "\n";
    $output .= "<span class=\"fieldset-title\">Legend<span class=\"required\">*</span>:</span> <input type=\"text\" class=\"m-mapTitle\" size=\"40\" maxlength=\"40\" name=\"coords[$i][title]\" />" . "\n";
    $output .= "</div>" . "\n";
    $output .= "<div class=\"resizable-textarea\">" . "\n";
    $output .= "<span><textarea class=\"resizable m-mapCoord\" rows=\"5\" cols=\"60\" name=\"coords[$i][data]\"></textarea></span>" . "\n";
    $output .= "</div>" . "\n";

    $output .= "<div class=\"fieldset-extras\">" . "\n";
    $output .= "<span class=\"fieldset-title\">Shape:</span> <select class=\"m-mapShape\" name=\"coords[$i][shape]\">$marker_shape</select> <span class=\"fieldset-title\">Size:</span> <select class=\"m-mapSize\" name=\"coords[$i][size]\">$marker_size</select>" . "\n";
    $output .= "<span class=\"fieldset-title\">Color:</span> <input class=\"colorPicker\" type=\"text\" size=\"12\" maxlength=\"11\" name=\"coords[$i][color]\" value=\"0 0 0\" />" . "\n";
    $output .= "</div>" . "\n";
    $output .= "<button class=\"sprites clear clearself negative\">Clear</button>" . "\n";
    $output .= "</div>" . "\n";
  
    $output .= "</div>" . "\n";
  }

  return $output;
}

function partial_regions() {
  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    $output .= '<div class="form-item fieldset-regions">';

    $output .= '<button class="sprites removemore negative" data-type="regions">Remove</button>';

    $output .= '<h3><a href="#">Region '.($i+1).'</a></h3>' . "\n";
    $output .= '<div>' . "\n";
    $output .= '<div class="fieldset-taxon">' . "\n";
    $output .= '<span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="regions['.$i.'][title]" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="resizable-textarea">' . "\n";
    $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="regions['.$i.'][data]"></textarea></span>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '<div class="fieldset-extras">' . "\n";
    $output .= '<span class="fieldset-title">Color:</span> <input type="text" class="colorPicker" size="12" maxlength="11" name="regions['.$i.'][color]" value="150 150 150" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<button class="sprites clear clearself negative">Clear</button>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '</div>' . "\n";
  }

  return $output;
}

function partial_scales() {
  $output = '';

  $file_sizes = array(3,4,5);
  foreach($file_sizes as $size) {
    $checked = ($size == 3) ? " checked=\"checked\"" : "";
    $output .= "<input type=\"radio\" id=\"download-factor-".$size."\" name=\"download-factor\" value=\"".$size."\"".$checked." />";
    $output .= "<label for=\"download-factor-".$size."\">".$size."X</label>";
  }

  return $output;
}

function partial_filetypes() {
  $output = '';

  $file_types = array('svg', 'png', 'tif', 'kml');
  foreach($file_types as $type) {
    $checked = ($type == "svg") ? " checked=\"checked\"": "";
    $asterisk = ($type == "svg") ? "*" : "";
    $output .= "<input type=\"radio\" id=\"download-".$type."\" class=\"download-filetype\" name=\"download-filetype\" value=\"".$type."\"".$checked." />";
    $output .= "<label for=\"download-".$type."\">".$type.$asterisk."</label>";
  }

  return $output;
}

function rotation_values() {
  $output = "";

  for($i=0;$i<360;$i++) {
    if($i % 5 == 0) {
      $output .= "<li data-rotate=\"$i\"></li>";
    }
  }
  return $output;
}
?>