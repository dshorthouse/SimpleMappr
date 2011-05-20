<?php

define("NUMTEXTAREA", 3);

$projection_selections = array(
	'epsg:4326' => 'Geographic',
	'esri:102009' => 'NA Lambert',
	'esri:102014' => 'Europe Lambert',
	'epsg:3107' => 'South America Lambert',
	'esri:102024' => 'Africa Lambert',
	'epsg:3033' => 'Australia Lambert',
);

session_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="dc.title" content="SimpleMappr" />
<meta name="dc.subject" content="A publication-quality, point map application." />
<meta name="dc.creator" content="David P. Shorthouse" />
<meta name="dc.description" content="A publication-quality, point map application." />
<meta name="dc.format" scheme="IMT" content="text/html" />
<meta name="dc.type.documentType" content="Web Page" />
<title>SimpleMappr</title>
<link type="image/x-icon" href="favicon.ico" rel="SHORTCUT ICON" />
<link type="text/css" href="css/screen.css" rel="stylesheet" />
<link type="text/css" href="css/colorpicker.css" rel="stylesheet" />
<link type="text/css" href="css/jquery.Jcrop.css" rel="stylesheet" />
<link type="text/css" href="js/themes/base/ui.all.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/ui/ui.core.js"></script>
<script type="text/javascript" src="js/ui/ui.tabs.js"></script>
<script type="text/javascript" src="js/colorpicker.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo.js"></script>
<script type="text/javascript" src="js/jquery.Jcrop.min.js"></script>
<script type="text/javascript" src="js/jquery.textarearesizer.compressed.js"></script>
<script type="text/javascript" src="js/mapper.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
jQuery.extend(Mapper.settings, { "baseUrl": "http://<?php echo $_SERVER['HTTP_HOST']; ?>" });
//--><!]]>
</script>
</head>

<body>
<h1 id="site-title"><img src="images/logo.png" alt="SimpleMappr" /><span>SimpleMappr</span></h1>
<div id="site-tagline">point maps for publication</div>
<?php if(isset($_SESSION['simplemappr'])): ?>
<div id="site-logout">Welcome back <?php echo $_SESSION['simplemappr']['username']; ?> <span class="site-logout"><a href="/usermaps/?action=logout">Log Out</a></span></div>
<?php else: ?>
<div id="site-logout"><span class="site-login"><a href="#" onclick="javascript:tabSelector(3);return false;">Log In</a></span></div>
<?php endif; ?>
<div id="wrapper">

	<div id="tabs">

    	<ul class="navigation">
			<li><a href="#map-preview">Map Preview</a></li>
			<li><a href="#map-points">Data Layers</a></li>
			<li><a href="#map-regions">Shaded Regions</a></li>
			<li><a href="#map-mymaps" class="map-mymaps">My Maps</a></li>
			<li class="map-extras"><a href="tabs/help.html">Help</a></li>
			<li class="map-extras"><a href="#map-about">About</a></li>
			<li class="map-extras"><a href="tabs/api.html">API</a></li>
		</ul>

	<form id="form-mapper" action="application/" method="post">  


		<!-- about tab -->
		<div id="map-about">
		    <div id="general-about" class="panel">
			<p>Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result.</p>
			</div>

			<h2>History</h2>
			<p>The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History.</p>

			<h2>Acknowledgments</h2>
			<p>Underlying ArcView shapefiles were obtained from Natural Earth, <a href="http://www.naturalearthdata.com/" target="_blank">http://www.naturalearthdata.com/</a> and the mapping software used is MapServer, <a href="http://mapserver.org" target="_blank">http://mapserver.org</a> via PHP MapScript.</p>

			<h2>Code</h2>
			<p>The code behind SimpleMappr may be obtained at <a href="https://code.google.com/p/simplemappr/">https://code.google.com/p/simplemappr/</a>.</p>

			<!-- AddThis Button BEGIN -->
			<p><a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;username=xa-4bba91c46a265778"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/></a><script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=xa-4bba91c46a265778"></script></p>
			<!-- AddThis Button END -->

		            <p>
		            <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>
		            </p>
		</div>

    <!-- multipoint tab -->
	<div id="map-points">

		<div id="fieldSetsPoints">

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

      $collapsed = ($j != 0) ? ' collapsed' : '';

	  echo '<fieldset class="collapsible'.$collapsed.'">' . "\n";
	  echo '<legend><a href="#">Layer '.($j+1).'</a></legend>' . "\n";
	  echo '<div class="fieldset-wrapper">' . "\n";
	  echo '<div class="form-item">' . "\n";
	  echo '<div class="fieldset-taxon">' . "\n";
	  echo '<span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" name="coords['.$j.'][title]"></input>' . "\n";
	  echo '</div>' . "\n";
	  echo '<div class="resizable-textarea">' . "\n";
	  echo '<span>' . "\n";
	  $id = ($j == 0) ? ' id="firstTextBox"' : '';
	  echo '<textarea class="resizable m-mapCoord" rows="5" cols="60" name="coords['.$j.'][data]"'.$id.'></textarea>' . "\n";
	  echo '</span>' . "\n";
	  echo '</div>' . "\n";
	  echo '<div class="description">';
	  if($j==0) {
	    echo 'coordinates on separate lines in decimal degrees as <em>latitude longitude</em> (separated by a space, comma, or semicolon) <a href="#" onclick="javascript:tabSelector(4);return false;" class="help">examples</a>';
	  }
	  echo '</div>' . "\n";
	  echo '</div>' . "\n";
	  echo '<div class="fieldset-extras">' . "\n";
	  echo '<span class="fieldset-title">Shape:</span> <select class="m-mapShape" name="coords['.$j.'][shape]">'.$marker_shape.'</select> <span class="fieldset-title">Size:</span> <select class="m-mapSize" name="coords['.$j.'][size]">'.$marker_size.'</select>' . "\n";
	  echo '<span class="fieldset-title">Color:</span> <input class="colorPicker" type="text" size="12" name="coords['.$j.'][color]" value="0 0 0"></input>' . "\n";
	  echo '</div>' . "\n";
	  echo '</div>' . "\n";
	  echo '</fieldset>' . "\n";
	}

?>

		</div>

		<div id="addFieldset">
			<button id="addMore" class="positive">Add a layer</button>
		</div>

		<div class="submit">
			<button class="submitForm positive">Preview</button>
			<button class="clearLayers negative">Clear</button>
		</div>
		
    <!-- close multipoints tab -->
    </div>

	<!-- shaded regions tab -->
	<div id="map-regions">
		<div id="regions-introduction" class="panel">
			<p>Type political regions and select a color to shade <em>e.g.</em> North Carolina, Alberta, Ontario, South Dakota AND/OR bracket pipe- or space-separated State/Province codes prefixed by 3-letter ISO country code such as USA[NC|SD], CAN[AB ON]</p> 
		</div>

		<div id="fieldSetsRegions">
		<fieldset>
	    <legend>Shaded Regions</legend>
	    <div class="fieldset-wrapper">
		<div class="fieldset-taxon">
		  <span class="fieldset-title">Legend<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" name="regions[title]"></input>
		</div>
	    <div class="resizable-textarea">
	      <span>
	      <textarea class="resizable m-mapRegion" rows="5" cols="60" name="regions[data]"></textarea>
	      </span>
	    </div>
	    </div>

	    <div class="fieldset-extras">
	      <span class="fieldset-title">Color:</span> <input class="colorPicker" type="text" size="12" name="regions[color]" value="150 150 150"></input>
	    </div>
	    </fieldset>
	    </div>

	    <div class="submit">
			<button class="submitForm positive">Preview</button>
			<button class="clearRegions negative">Clear</button>
		</div>

	</div>
	<!-- close shaded regions tab -->

    <!-- map preview tab -->
	<div id="map-preview">
		<div id="mapWrapper">
			<div id="actionsBar">
					  <ul class="dropdown">
					  <li><a href="#" onclick="javascript:return false;" class="toolsZoomIn" rel="zoomIn" title="zoom in"></a></li>
					  <li><a href="#" onclick="javascript:return false;" class="toolsZoomOut" rel="zoomOut" title="zoom out"></a></li>
					  <li><a href="#" onclick="javascript:return false;" class="rotateAnticlockwise" title="rotate counterclockwise"></a>
					    <ul class="sub_menu">
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateAC5" rel="rotateac5">5<sup>o</sup></a></li>
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateAC10" rel="rotateac10">10<sup>o</sup></a></li>
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateAC15" rel="rotateac15">15<sup>o</sup></a></li>
						</ul>
					  </li>
					  <li><a href="#" onclick="javascript:return false;" class="rotateClockwise" title="rotate clockwise"></a>
					    <ul class="sub_menu">
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateC5" rel="rotatec5">5<sup>o</sup></a></li>
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateC10" rel="rotatec10">10<sup>o</sup></a></li>
						  <li><a href="#" onclick="javascript:return false;" class="toolsRotateC15" rel="rotatec15">15<sup>o</sup></a></li>
						</ul>
					  </li>
					  <li><a href="#" onclick="javascript:return false;" class="toolsCrop" rel="crop" title="crop"></a></li>
					  <li><a href="#" onclick="javascript:return false;" class="toolsRefresh" rel="refresh" title="refresh"></a></li>
					  <li><a href="#" onclick="javascript:return false;" class="toolsRebuild" rel="rebuild" title="rebuild"></a></li>
					</ul>
					
					<div id="badRecordsWarning"><a href="#" onclick="javascript:return false;" class="toolsBadRecords" rel="badRecords">Records Out of Range</a></div>
			</div>
			<div class="clear"></div>
			
			<div id="map">
				<div id="mapImage">
					<div id="mapControlsTransparency"></div>
					<div id="mapControls">
						  <a href="#" class="controls" id="arrow-up" onclick="javascript:return false;"></a>
						  <a href="#" class="controls" id="arrow-right" onclick="javascript:return false;"></a>
						  <a href="#" class="controls" id="arrow-down" onclick="javascript:return false;"></a>
						  <a href="#" class="controls" id="arrow-left" onclick="javascript:return false;"></a>
					</div>
					<div id="mapOutput"></div>
				</div>
				<div id="mapScale"></div>
			</div>
			
			<div id="mapTools">

			  <ul>
				<li><a href="#mapLegend">Legend</a></li>
				<li><a href="#mapOptions">Options</a></li>
				<li><a href="#mapExport">Download</a></li>
				<?php if(isset($_SESSION['simplemappr'])): ?>
				<li><a href="#mapSave">Save</a></li>
				<?php endif; ?>
			  </ul>

			  <div id="mapLegend">
			  </div>

			  <div id="mapOptions">
				<h2>Layers</h2>
			    <ul>
					<li><input type="checkbox" id="stateprovince" class="layeropt" name="layers[stateprovinces]"></input> State/Provinces</li>
					<li><input type="checkbox" id="placenames" class="layeropt" name="layers[placenames]"></input> place names</li>
					<li><input type="checkbox" id="lakes"  class="layeropt" name="layers[lakes]"></input> lakes</li>
					<li><input type="checkbox" id="rivers"  class="layeropt" name="layers[rivers]"></input> rivers</li>
				</ul>
			    <h2>Options</h2>
			    <ul>
					<li><input type="checkbox" id="scalebar"  class="layeropt" name="options[scalebar]"></input> scalebar</li>
					<li><input type="checkbox" id="graticules"  class="layeropt" name="layers[grid]"></input> graticules</li>
				</ul>
				<h2>Projection*</h2>
				<ul>
				  <li>
						<select id="projection" name="projection">
					<?php
					  foreach($projection_selections as $value => $name) {
						$selected = ($name == 'Geographic') ? ' selected="selected"': '';
						echo '<option value="'.$value.'"'.$selected.'>'.$name.'</option>' . "\n";
					  }
					?>
						</select>
				  </li>
				</ul>
		                <p>*zoom prior to setting projection</p>
			  </div> <!-- /mapOptions -->

			  <div id="mapExport">
			    <div id="mapCropMessage">map will be cropped</div>
			    <ul>
				  <li><label for="download-factor">Download size</label>
					  <select id="download-factor" name="download_factor">
						<option value="">--select--</option>
						<option value="3" selected="selected">3X</option>
						<option value="4">4X</option>
						<option value="5">5X</option>
					  </select>
				  </li>
				  <li><input type="checkbox" id="border" name="options[border]"></input> include border</li>
				  <li><input type="checkbox" id="legend" name="options[legend]"></input> include legend</li>
				</ul>
				<ul>
				  <li class="export-png"><a href="#" onclick="javascript:return false;" class="toolsPng" rel="savePng"> png</a></li>
				  <li class="export-tiff"><a href="#" onclick="javascript:return false;" class="toolsTiff" rel="saveTiff"> tif</a></li>
				  <li class="export-svg"><a href="#" onclick="javascript:return false;" class="toolsSvg" rel="saveSvg"> svg*</a> (recommended)</li>
				</ul>
				<p>*Download does not include scale/legend</p>
				<ul>
				  <li class="export-kml"><a href="#" onclick="javascript:return false;" class="toolsKml" rel="saveKml"> kml (Google Earth)</a></li>
				</ul>
			  </div> <!-- /mapExport -->
			
				<?php if(isset($_SESSION['simplemappr'])): ?>
					<div id="mapSave">
						<div class="fieldset-wrapper">
						<div class="fieldset-taxon">
						  <span class="fieldset-title">Title<span class="required">*</span>:</span> <input type="text" class="m-mapSaveTitle" size="20" name="save[title]"></input>
						</div>
						<div class="submit">
							<button class="saveForm positive">Save</button>
						</div>
						</div>
					</div> <!-- /mapSave -->
				<?php endif;?>
				
			</div> <!-- /mapTools -->

			<div id="badRecordsViewer">
				<div id="badRecordsClose"><a href="#" onclick="javascript:return false">Close</a></div>
				<div id="badRecords"></div>
			</div>
			
		</div>
	</div>

    <!-- my maps tab -->
    <div id="map-mymaps">
		<?php if(!isset($_SESSION['simplemappr'])): ?>
		   	<div class="panel">
				<p>Save and reload your map data or create a generic template.</p> 
			</div>
		   <iframe src="http://simplemappr.rpxnow.com/openid/embed?token_url=http%3A%2F%2Fwww.simplemappr.net%2Fusermaps%2Frpx.php"  scrolling="no"  frameBorder="no"  allowtransparency="true" style="width:400px;height:240px;border:none"></iframe> 
		<?php else: ?>
			<div id="usermaps"></div>
		<?php endif; ?>
	</div>

	<!-- hidden form elements for map preview -->
	<input type="hidden" id="download" name="download"></input>
	<input type="hidden" id="output" name="output"></input>
	
	<!-- bounding box of map image in whatever projection map is in -->
	<input type="hidden" id="bbox_map" name="bbox_map"></input>

	<!-- coordinates of bounding box in pixels where top left is (x,y) and bottom right is (x2,y2)-->
	<input type="hidden" id="bbox_rubberband" name="bbox_rubberband"></input>
	
    <input type="hidden" id="pan" name="pan"></input>
	<input type="hidden" id="zoom_out" name="zoom_out"></input>
	<input type="hidden" id="crop" name="crop"></input>
	<input type="hidden" id="rotation" name="rotation"></input>
	
	<!-- selected tab -->
	<input type="hidden" id="selectedtab" name="selectedtab"></input>

	</form>

	<!-- close tabs wrapper -->
	</div>

</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-946452-10");
pageTracker._setDomainName(".simplemappr.net");
pageTracker._trackPageview();
} catch(err) {}</script>
<script type="text/javascript">
  var uservoiceOptions = {
    key: 'simplemappr',
    host: 'simplemappr.uservoice.com', 
    forum: '47855',
    alignment: 'right',
    background_color:'#358fd9', 
    text_color: 'white',
    hover_color: '#00488f',
    lang: 'en',
    showTab: true
  };
  function _loadUserVoice() {
    var s = document.createElement('script');
    s.src = ("https:" == document.location.protocol ? "https://" : "http://") + "uservoice.com/javascripts/widgets/tab.js";
    document.getElementsByTagName('head')[0].appendChild(s);
  }
  _loadSuper = window.onload;
  window.onload = (typeof window.onload != 'function') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
</script>
</body>
</html>
