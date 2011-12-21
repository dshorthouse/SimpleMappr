<?php
require_once('config/conf.php');
$header = set_up();
header('Content-Type: text/html; charset=utf-8');
$language = isset($_GET["lang"]) ? $_GET["lang"] : 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
<meta charset="UTF-8">
<meta name="description" content="<?php echo _("A point map application for publications and presentations."); ?>" />
<meta name="keywords" content="<?php echo _("publication,presentation,map,georeference"); ?>" />
<meta name="author" content="David P. Shorthouse" />
<title>SimpleMappr</title>
<link type="image/x-icon" href="favicon.ico" rel="SHORTCUT ICON" />
<?php $header[0]->getCSSHeader(); ?>
</head>
<?php flush(); ?>
<body>
<div id="header" class="clearfix">
<h1 id="site-title" class="sprites-after">SimpleMapp<span>r</span></h1>
<div id="site-tagline"><?php echo _("point maps for publication and presentation"); ?></div>
<div id="site-languages">
<ul><?php foreach($header[1] as $key => $langs): ?><?php $selected = ''; if($key == $language) { $selected = ' class="selected" '; } ?><li><?php echo '<a href="/?lang='.$key.'"'.$selected.'>'.$langs['native'].'</a>'; ?></li><?php endforeach; ?></ul>
</div>
<?php if(isset($_SESSION['simplemappr'])): ?>
<div id="site-user"><?php echo $_SESSION['simplemappr']['username']; ?></div>
<?php endif; ?>
<div id="site-session">
<?php if(isset($_SESSION['simplemappr'])): ?>
<a class="sprites-before logout" href="/logout/"><?php echo _("Sign Out"); ?></a>
<?php else: ?>
<a class="sprites-before login" href="#"><?php echo _("Sign In"); ?></a>
<?php endif; ?>
</div>
</div>
<div id="wrapper">
<div id="initial-message" class="ui-corner-all ui-widget-content"><span><?php echo _("Building application..."); ?></span></div>
<div id="tabs">
<ul class="navigation">
<li><a href="#map-preview"><?php echo _("Preview"); ?></a></li>
<li><a href="#map-points"><?php echo _("Point Data"); ?></a></li>
<li><a href="#map-regions"><?php echo _("Regions"); ?></a></li>
<li><a href="#map-mymaps" class="sprites-before map-mymaps"><?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?><?php echo _("All Maps"); ?><?php else: ?><?php echo _("My Maps"); ?><?php endif; ?></a></li>
<?php if(isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1): ?>
<li><a href="#map-users" class="sprites-before map-users"><?php echo _("Users"); ?></a></li>
<?php endif; ?>
<?php $qlang = isset($_GET['lang']) ? "?lang=" . $_GET["lang"] : ""; ?>
<li class="map-extras"><a href="tabs/help.php<?php echo $qlang; ?>" class="sprites-before map-myhelp"><?php echo _("Help"); ?></a></li>
<li class="map-extras"><a href="tabs/about.php<?php echo $qlang; ?>"><?php echo _("About"); ?></a></li>
<li class="map-extras"><a href="tabs/feedback.php<?php echo $qlang; ?>"><?php echo _("Feedback"); ?></a></li>
<li class="map-extras"><a href="tabs/api.php<?php echo $qlang; ?>"><?php echo _("API"); ?></a></li>
</ul>
<form id="form-mapper" action="application/" method="post" autocomplete="off">

<!-- multipoint tab -->
<div id="map-points">
<div id="general-points" class="panel ui-corner-all">
<p><?php echo _("Type geographic coordinates on separate lines in decimal degrees as latitude longitude (separated by a space, comma, or semicolon)"); ?> <a href="#" class="sprites-before help show-examples"><?php echo _("examples"); ?></a></p>
</div>
<div id="fieldSetsPoints" class="fieldSets">
<?php echo partial_layers(); ?>
</div>
<div class="addFieldset"><button class="sprites-before addmore positive ui-corner-all" data-type="coords"><?php echo _("Add a layer"); ?></button></div>
<div class="submit"><button class="sprites-before submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button class="sprites-before clear clearLayers negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<!-- shaded regions tab -->
<div id="map-regions">
<div id="regions-introduction" class="panel ui-corner-all">
<?php $tabIndex = (isset($_SESSION['simplemappr']) && $_SESSION['simplemappr']['uid'] == 1) ? 5 : 4; ?>
<p><?php echo _("Type countries as Mexico, Venezuela AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code <em>e.g.</em>USA[VA], CAN[AB ON]."); ?> <a href="#" data-tab="<?php echo $tabIndex; ?>" class="sprites-before help show-codes"><?php echo _("codes"); ?></a></p>
</div>
<div id="fieldSetsRegions" class="fieldSets">
<?php echo partial_regions(); ?>
</div>
<div class="addFieldset"><button class="sprites-before addmore positive ui-corner-all" data-type="regions"><?php echo _("Add a region"); ?></button></div>
<div class="submit"><button class="sprites-before submitForm positive ui-corner-all"><?php echo _("Preview"); ?></button><button class="sprites-before clear clearRegions negative ui-corner-all"><?php echo _("Clear all"); ?></button></div>
</div>

<!-- map preview tab -->
<div id="map-preview">
<div id="mapWrapper">
<div id="actionsBar" class="ui-widget-header ui-corner-all ui-helper-clearfix">
<ul>
<li><a href="#" class="sprites tooltip toolsZoomIn" title="<?php echo _("zoom in ctrl+"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsZoomOut" title="<?php echo _("zoom out ctrl-"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsCrop" title="<?php echo _("crop ctrl+x"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsQuery" title="<?php echo _("fill regions"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRefresh" title="<?php echo _("refresh ctrl+r"); ?>"></a></li>
<li><a href="#" class="sprites tooltip toolsRebuild" title="<?php echo _("rebuild ctrl+n"); ?>"></a></li>
</ul>
<h3 id="mapTitle"></h3>
<ul id="map-saveDialog">
<?php if(isset($_SESSION['simplemappr'])): ?>
<li><a class="sprites-before tooltip map-saveItem map-save" href="#" title="<?php echo _("save ctrl+s"); ?>"><?php echo _("Save"); ?></a></li>
<li><a class="sprites-before tooltip map-saveItem map-embed" href="#" title="<?php echo _("embed"); ?>" data-mid=""><?php echo _("Embed"); ?></a></li>
<?php endif; ?>
<li><a class="sprites-before tooltip map-saveItem map-download" href="#" title="<?php echo _("download ctrl+d"); ?>"><?php echo _("Download"); ?></a></li>
</ul>
</div>
<div id="map">
<div id="mapImage">
<div id="mapControls">
<div class="viewport">
<ul class="overview"></ul>
</div>
<div class="dot"></div>
<div class="overlay">
<a href="#" class="sprites tooltip controls arrows up" data-pan="up" title="<?php echo _("pan up"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows right" data-pan="right" title="<?php echo _("pan right"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows down" data-pan="down" title="<?php echo _("pan down"); ?>"></a>
<a href="#" class="sprites tooltip controls arrows left" data-pan="left" title="<?php echo _("pan left"); ?>"></a>
</div>
<div class="thumb ui-corner-all ui-widget-header"></div>
</div>
<div id="badRecordsWarning"><a href="#" class="sprites-before toolsBadRecords"><?php echo _("Records Out of Range"); ?></a></div>
<div id="mapOutput"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Building preview..."); ?></span></div>
</div>
<div id="mapScale"></div>
<div id="mapToolsCollapse" class="mapTools-default ui-widget-header ui-corner-left"><a href="#" class="sprites tooltip" title="<?php echo _("expand/collpase"); ?>"></a></div>
</div>
<div id="mapTools">
<ul>
<li><a href="#mapOptions"><?php echo _("Settings"); ?></a></li>
<li><a href="#mapLegend"><?php echo _("Legend"); ?></a></li>
</ul>
<div id="mapLegend"><p><em><?php echo _("legend will appear here"); ?></em></p></div>
<div id="mapOptions">
<h2><?php echo _("Layers"); ?></h2>
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="stateprovince" class="layeropt" name="layers[stateprovinces]" /> <?php echo _("State/Provinces"); ?></li>
<li><input type="checkbox" id="lakesOutline" class="layeropt" name="layers[lakesOutline]" /> <?php echo _("lakes (outline)"); ?></li>
<li><input type="checkbox" id="lakes" class="layeropt" name="layers[lakes]" /> <?php echo _("lakes (greyscale)"); ?></li>
<li><input type="checkbox" id="rivers" class="layeropt" name="layers[rivers]" /> <?php echo _("rivers"); ?></li>
<li><input type="checkbox" id="relief" class="layeropt" name="layers[relief]" /> <?php echo _("relief"); ?></li>
<li><input type="checkbox" id="reliefgrey" class="layeropt" name="layers[reliefgrey]" /> <?php echo _("relief (greyscale)"); ?></li>
</ul>
<h2><?php echo _("Labels"); ?></h2>
<ul class="columns ui-helper-clearfix">
<li><input type="checkbox" id="countrynames" class="layeropt" name="layers[countrynames]" /> <?php echo _("Countries"); ?></li>
<li><input type="checkbox" id="stateprovincenames" class="layeropt" name="layers[stateprovnames]" /> <?php echo _("State/Provinces"); ?></li>
<li><input type="checkbox" id="lakenames" class="layeropt" name="layers[lakenames]" /> <?php echo _("lakes"); ?></li>
<li><input type="checkbox" id="rivernames" class="layeropt" name="layers[rivernames]" /> <?php echo _("rivers"); ?></li>
<li><input type="checkbox" id="placenames" class="layeropt" name="layers[placenames]" /> <?php echo _("places"); ?></li>
<li><input type="checkbox" id="physicalLabels" class="layeropt" name="layers[physicalLabels]" /> <?php echo _("physical"); ?></li>
<li><input type="checkbox" id="marineLabels" class="layeropt" name="layers[marineLabels]" /> <?php echo _("marine"); ?></li>
</ul>
<h2><?php echo _("Options"); ?></h2>
<ul>
<li><input type="checkbox" id="graticules"  class="layeropt" name="layers[grid]" /> <?php echo _("graticules (grid)"); ?>
<div id="graticules-selection">
<input type="radio" id="gridspace" class="gridopt" name="gridspace" value="" checked="checked" /> <?php echo _("fixed"); ?>
<input type="radio" id="gridspace-5" class="gridopt" name="gridspace" value="5" /> 5<sup>o</sup>
<input type="radio" id="gridspace-10" class="gridopt" name="gridspace" value="10" /> 10<sup>o</sup>
</div>
</li>
</ul>
<h3><?php echo _("Line Thickness"); ?></h3>
<div id="border-slider"></div>
<h2><?php echo _("Projection"); ?>*</h2>
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
<p>*<?php echo _("zoom prior to setting projection"); ?></p>
</div>
</div>
</div>
</div>

<!-- my maps tab -->
<div id="map-mymaps">
<?php if(!isset($_SESSION['simplemappr'])): ?>
<div class="panel ui-corner-all">
<p><?php echo _("Save and reload your map data or create a generic template."); ?></p> 
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
<div id="badRecordsViewer" title="<?php echo _("Records out of range"); ?>"><div id="badRecords"></div></div>
<div id="mapSave" title="<?php echo _("Save"); ?>">
<p>
<label for="m-mapSaveTitle"><?php echo _("Title"); ?><span class="required">*</span></label>
<input type="text" id="m-mapSaveTitle" class="m-mapSaveTitle" size="30" maxlength="30" />
</p>
</div>
<div id="mapExport" title="<?php echo _("Download"); ?>">
<div class="download-dialog">
<p>
<label for="file-name"><?php echo _("File name"); ?></label>
<input type="text" id="file-name" maxlength="30" size="30" />
</p>
<fieldset>
<legend><?php echo _("File type"); ?></legend>
<?php echo partial_filetypes(); ?>
</fieldset>
<fieldset>
<legend><?php echo _("Options"); ?></legend>
<p id="mapCropMessage" class="sprites-before"><?php echo _("map will be cropped"); ?></p>
<div class="download-options">
<?php echo partial_scales(); ?>
<div id="scale-measure"><?php echo sprintf(_("Dimensions: %s"), '<span></span>')?></div>
</div>
<input type="checkbox" id="border" />
<label for="border"><?php echo _("include border"); ?></label>
<input type="checkbox" id="legend" disabled="disabled" />
<label for="legend"><?php echo _("embed legend"); ?></label>
<input type="checkbox" id="scalebar" disabled="disabled" />
<label for="scalebar"><?php echo _("embed scalebar"); ?></label>
</fieldset>
<p>*<?php echo _("does not include scalebar, legend, or relief layers"); ?></p>
</div>
<div class="download-message"><?php echo _("Building file for download..."); ?></div>
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
<input type="hidden" name="width" />
<input type="hidden" name="height" />
<input type="hidden" name="download_filetype" />
<input type="hidden" name="grid_space" />
<input type="hidden" name="options[border]" />
<input type="hidden" name="options[legend]" />
<input type="hidden" name="options[scalebar]" />
<input type="hidden" name="border_thickness" id="border_thickness" />
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
<div id="mapper-message" class="ui-state-error" title="<?php echo _("Warning"); ?>"></div>
<div id="button-titles" class="hidden-message">
  <span class="save"><?php echo _("Save"); ?></span>
  <span class="cancel"><?php echo _("Cancel"); ?></span>
  <span class="download"><?php echo _("Download"); ?></span>
  <span class="delete"><?php echo _("Delete"); ?></span>
</div>
<div id="mapper-loading-message" class="hidden-message"><?php echo _("Building preview..."); ?></div>
<div id="mapper-saving-message" class="hidden-message"><?php echo _("Saving..."); ?></div>
<div id="mapper-missing-legend" class="hidden-message"><?php echo _("You are missing a legend for at least one of your Point Data or Regions layers."); ?></div>
<div class="usermaps-loading hidden-message"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Loading your maps..."); ?></span></div>
<div class="userdata-loading hidden-message"><span class="mapper-loading-message ui-corner-all ui-widget-content"><?php echo _("Loading user list..."); ?></span></div>
<div id="mapper-message-delete" class="ui-state-highlight hidden-message" title="<?php echo _("Delete"); ?>"><?php echo _("Are you sure you want to delete"); ?> <span></span>?</div>
<div id="mapper-legend-message" class="hidden-message"><?php echo _("legend will appear here"); ?></div>
<div id="mapper-message-help" class="ui-state-highlight hidden-message" title="<?php echo _("Example Coordinates"); ?>"></div>
<div id="mapEmbed" class="ui-state-highlight hidden-message" title="<?php echo _("Embed"); ?>">
  <div class="header"><h2><?php echo _('Image'); ?></h2></div>
  <p><input id="embed-img" type="text" size="65" value="" /></p>
  <p><strong><?php echo _("Additional parameters"); ?></strong>:<br><span class="indent"><?php echo _("width, height"); ?> (<em>e.g.</em> /map/<span class="mid"></span>?width=200&amp;height=150)</span></p>
  <div class="header"><h2><?php echo _('KML'); ?></h2></div>
  <p><input id="embed-kml" type="text" size="65" value="" /></p>
  <div class="header"><h2><?php echo _('GeoJSON'); ?></h2></div>
  <p><input id="embed-json" type="text" size="65" value="" /></p>
  <p><strong><?php echo _("Additional parameters"); ?></strong>:<br><span class="indent"><?php echo _("callback"); ?> (<em>e.g.</em> /map/<span class="mid"></span>.json?callback=myCoolCallback)</span></p>
</div>
<div id="colorpicker"><div class="colorpicker colorpicker_background"><div class="colorpicker_color"><div class="colorpicker"><div class="colorpicker"></div></div></div><div class="colorpicker_hue"><div class="colorpicker"></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="sprites-before colorpicker_submit"><?php echo _("Apply"); ?></div></div></div>
<?php $header[0]->getJSFooter();?>
</body>
</html>
<?php

function set_up() {
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
  } else {
    $host = explode(".", $_SERVER['HTTP_HOST']);
    if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
      header('Location: http://www.simplemappr.net/');
    } else {
      require_once('lib/mapprservice.usersession.class.php');
      require_once('lib/mapprservice.header.class.php');
      require_once('lib/mapprservice.class.php');

      USERSESSION::update_activity();

      return array(new HEADER, USERSESSION::$accepted_languages);
    }
  }
}

function partial_layers() {
  //marker sizes and shapes
  $marker_size  = '<option value="">'._("--select--").'</option>';
  $marker_size .= '<option value="6">6pt</option>';
  $marker_size .= '<option value="8">8pt</option>';
  $marker_size .= '<option value="10" selected="selected">10pt</option>';
  $marker_size .= '<option value="12">12pt</option>';
  $marker_size .= '<option value="14">14pt</option>';
  $marker_size .= '<option value="16">16pt</option>';

  $marker_shape  = '<option value="">'._("--select--").'</option>';
  $marker_shape .= '<option value="plus">'._("plus").'</option>';
  $marker_shape .= '<option value="cross">'._("cross").'</option>';
  $marker_shape .= '<optgroup label="'._("solid").'">';
  $marker_shape .= '<option value="circle" selected="selected">'._("circle (s)").'</option>';
  $marker_shape .= '<option value="star">'._("star (s)").'</option>';
  $marker_shape .= '<option value="square">'._("square (s)").'</option>';
  $marker_shape .= '<option value="triangle">'._("triangle (s)").'</option>';
  $marker_shape .= '</optgroup>';
  $marker_shape .= '<optgroup label="'._("open").'">';
  $marker_shape .= '<option value="opencircle">'._("circle (o)").'</option>';
  $marker_shape .= '<option value="openstar">'._("star (o)").'</option>';
  $marker_shape .= '<option value="opensquare">'._("square (o)").'</option>';
  $marker_shape .= '<option value="opentriangle">'._("triangle (o)").'</option>';
  $marker_shape .= '</optgroup>';

  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    
    $output .= '<div class="form-item fieldset-points">';

    $output .= '<button class="sprites-before removemore negative ui-corner-all" data-type="coords">'._("Remove").'</button>';
  
    $output .= '<h3><a href="#">'.sprintf(_("Layer %d"),$i+1).'</a></h3>' . "\n";
    $output .= '<div>' . "\n";
    $output .= '<div class="fieldset-taxon">' . "\n";
    $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="coords['.$i.'][title]" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="resizable-textarea">' . "\n";
    $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="coords['.$i.'][data]"></textarea></span>' . "\n";
    $output .= '</div>' . "\n";

    $output .= '<div class="fieldset-extras">' . "\n";
    $output .= '<span class="fieldset-title">'._("Shape").':</span> <select class="m-mapShape" name="coords['.$i.'][shape]">'.$marker_shape.'</select> <span class="fieldset-title">'._("Size").':</span> <select class="m-mapSize" name="coords['.$i.'][size]">'.$marker_size.'</select>' . "\n";
    $output .= '<span class="fieldset-title">'._("Color").':</span> <input class="colorPicker" type="text" size="12" maxlength="11" name="coords['.$i.'][color]" value="0 0 0" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<button class="sprites-before clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '</div>' . "\n";
  }

  return $output;
}

function partial_regions() {
  $output = '';

  for($i=0;$i<=NUMTEXTAREA-1;$i++) {
    $output .= '<div class="form-item fieldset-regions">';

    $output .= '<button class="sprites-before removemore negative ui-corner-all" data-type="regions">'._("Remove").'</button>';

    $output .= '<h3><a href="#">'.sprintf(_("Region %d"), $i+1).'</a></h3>' . "\n";
    $output .= '<div>' . "\n";
    $output .= '<div class="fieldset-taxon">' . "\n";
    $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="regions['.$i.'][title]" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="resizable-textarea">' . "\n";
    $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="regions['.$i.'][data]"></textarea></span>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '<div class="fieldset-extras">' . "\n";
    $output .= '<span class="fieldset-title">'._("Color").':</span> <input type="text" class="colorPicker" size="12" maxlength="11" name="regions['.$i.'][color]" value="150 150 150" />' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<button class="sprites-before clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
    $output .= '</div>' . "\n";
  
    $output .= '</div>' . "\n";
  }

  return $output;
}

function partial_scales() {
  $output = '';

  $file_sizes = array(1,3,4,5);
  foreach($file_sizes as $size) {
    $checked = ($size == 1) ? ' checked="checked"' : '';
    $output .= '<input type="radio" id="download-factor-'.$size.'" class="download-factor" name="download-factor" value="'.$size.'"'.$checked.' />';
    $output .= '<label for="download-factor-'.$size.'">'.$size.'X</label>';
  }

  return $output;
}

function partial_filetypes() {
  $output = '';
  $file_types = array('svg', 'png', 'tif', 'pptx', 'docx', 'kml');
  foreach($file_types as $type) {
    $extra = '';
    $checked = ($type == "svg") ? ' checked="checked"': '';
    $output .= '<input type="radio" id="download-'.$type.'" class="download-filetype" name="download-filetype" value="'.$type.'"'.$checked.' />';
    $asterisk = ($type == "svg" || $type == "kml") ? '*' : '';
    if($type == 'kml') { $extra = ' (Google Earth)'; }
    if($type == 'pptx') { $extra = ' (PowerPoint)'; }
    if($type == 'docx') { $extra = ' (Word)'; }
    $output .= '<label for="download-'.$type.'">'.$type.$asterisk.$extra.'</label>';
  }

  return $output;
}
?>