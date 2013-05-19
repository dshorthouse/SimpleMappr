<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root.'/lib/mappr.class.php');
require_once($root.'/lib/session.class.php');
Session::select_locale();
?>
<!-- api tab -->
<style type="text/css">
#general-base{margin-top:20px;}
#map-api p{font-size:0.75em;}
#map-api dl{font-size:0.75em;}
#map-api dt{font-weight:bold;}
#map-api dd{margin-bottom:1.5em;}
#map-api span.api-example{color:green;display:block;}
#map-api span.api-example a{color:green;}
#map-api span.api-output{color:red;display:block;}
#map-api span.api-output a{color:red;}
</style>
<div id="map-api">
  <div id="general-api" class="panel ui-corner-all">
    <p><?php echo _("A simple, restful API may be used with Internet accessible, tab-separated text files, a collection of coordinates, or by sending files with additional parameters outlined below."); ?></p>
  </div>
  <p><em>e.g.</em> http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?<br>url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/public/files/demo.txt'); ?>&amp;<br>shape[0]=square&amp;size[0]=10&amp;color[0]=20,20,20&amp;<br>shape[1]=triangle&amp;size[1]=10&amp;color[1]=40,40,40&amp;<br>shape[2]=star&amp;size[2]=14&amp;color[2]=60,60,60&amp;<br>width=500&amp;height=300&amp;<br>bbox=-130,40,-60,50&amp;<br>layers=lakes,stateprovinces&amp;graticules=true&amp;projection=esri:102009&amp;legend=true&amp;<br>shade[places]=Alberta,USA[MT|WA]&amp;shade[title]=Selected Regions&amp;shade[color]=150,150,150</p>
  <p><strong><?php echo _("Produces"); ?></strong></p>
  <p><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/public/images/api.png" width="500" height="300" alt="<?php echo _("My Map"); ?>" /></p>

  <div class="header">
    <h2><?php echo _("Base URL"); ?></h2>
  </div>

  <div id="general-base" class="panel ui-corner-all">
    <p>http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/</p>
  </div>

  <div class="header">
    <h2><?php echo _("Parameters"); ?></h2>
  </div>

  <dl>
    <dt>ping</dt>
    <dd><?php echo _("if ping=true is included, a JSON response will be produced in place of an image."); ?>
      <span class="api-output"><em>e.g.</em> {"status" : "ok"}</span>
    </dd>

    <dt>url</dt>
    <dd><?php echo _("a URL-encoded, remote tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated points."); ?>
      <span class="api-example"><em>e.g.</em> url=<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/public/files/demo.txt"><?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/public/files/demo.txt'); ?></a></span>
      <br>
      <?php echo _("a URL-encoded, remote GeoRSS feed."); ?> 
      <span class="api-example"><em>e.g.</em> url=<a href="http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml"><?php echo urlencode('http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml'); ?></a></span>
    </dd>

    <dt>file</dt>
    <dd><?php echo sprintf(_("Note: requires a POST request to http://%s/api/ with an enctype set to multipart/form-data."), $_SERVER['HTTP_HOST']) ?><br />
        <?php echo _("tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated. The initial response will be JSON with an imageURL element and an expiry element, which indicates when the file will likely be deleted from the server."); ?>
    <span class="api-example"><a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/demo.txt">Example file</a></span>
    <span class="api-output"><em>e.g.</em> { "imageURL" : "<?php echo MAPPR_MAPS_URL; ?>/50778960_464f_0.png", "expiry" : "<?php echo date('c', time() + (6 * 60 * 60)); ?>" }</span>
    </dd>

    <dt>points[x]</dt>
    <dd><?php echo _("single or multiple markers written as latitude,longitude in decimal degrees, DDMMSS, or DD mm.mm. Multiple markers are separated by line-breaks, \\n and these are best used in a POST request. If a POST request is used, the initial response will be JSON as above."); ?> <span class="api-example"><em>e.g.</em> points[0]=45,-120 or points[0]=45°52'30"N,120W or points[0]=45°52.5N,120W, or points[0]=45,-120\n45,-110\n45,-125\n42,-100&amp;points[1]=44,-100</span></dd>

    <dt>shape[x]</dt>
    <dd><?php echo _("shape of marker for column x; options are circle, square, triangle, star, opencircle, opensquare, opentriangle, openstar"); ?> <span class="api-example"><em>e.g.</em> shape[0]=circle</span></dd>

    <dt>size[x]</dt>
    <dd><?php echo _("integer-based point size of marker in column x"); ?> <span class="api-example"><em>e.g.</em> size[1]=10</span></dd>

    <dt>color[x]</dt>
    <dd><?php echo _("comma-separated RGB colors for marker in column x"); ?> <span class="api-example"><em>e.g.</em> color[2]=255,0,0</span></dd>

    <dt>outlinecolor</dt>
    <dd><?php echo ("comma-separated RGB colors for halo around all solid markers"); ?> <span class="api-example"><em>e.g.</em> outlinecolor=40,40,40</span></dd>

    <dt>zoom</dt>
    <dd><?php echo ("integer from 1 to 10, centered on the geographic midpoint of all coordinates"); ?> <span class="api-example"><em>e.g.</em> zoom=8</span></dd>

    <dt>bbox</dt>
    <dd><?php echo sprintf(_("comma-separated bounding box in decimal degrees %s"), "(minx, miny, maxx, maxy)"); ?> <span class="api-example"><em>e.g.</em> bbox=-130,40,-60,50</span></dd>

    <dt>shade[places]</dt>
    <dd><?php echo _("comma-separated State, Province or Country names or the three-letter ISO country code with pipe-separated States or Provinces flanked by brackets"); ?> <span class="api-example"><em>e.g.</em> shade[places]=Alberta,USA[MT|WA]</span></dd>

    <dt>shade[title]</dt>
    <dd><?php echo _("the title for the shaded places"); ?> <span class="api-example"><em>e.g.</em> shade[title]=Occurrence</span></dd>

    <dt>shade[color]</dt>
    <dd><?php echo _("comma-separated RGB fill colors for shaded places"); ?> <span class="api-example"><em>e.g.</em> shade[color]=150,150,150</span></dd>

    <dt>layers</dt>
    <dd><?php echo _("comma-separated cultural or physical layers; options are relief, stateprovinces, lakes, rivers, oceans, placenames"); ?> <span class="api-example"><em>e.g.</em> layers=lakes,stateprovinces</span></dd>

    <dt>projection</dt>
    <dd><?php echo sprintf(_("the output projection in either EPSG or ESRI references. See %s for spatial references. Accepted projections are:"), "<a href=\"http://spatialreference.org/\">http://spatialreference.org/</a>"); ?> <?php foreach(MAPPR::$accepted_projections as $key => $value) { print $key . ' (=' . $value['name'] . '), '; } ?><span class="api-example"><em>e.g.</em> projection=esri:102009</span></dd>
	
	<dt>origin</dt>
	<dd><?php echo _("longitude of natural origin used in Lambert projections"); ?> <span class="api-example"><em>e.g.</em> origin=-120</span></dd>

    <dt>graticules</dt>
    <dd><?php echo _("display the graticules"); ?> <span class="api-example"><em>e.g.</em> graticules=true</span></dd>

    <dt>spacing</dt>
    <dd><?php echo _("display the graticules with defined spacing in degrees"); ?> <span class="api-example"><em>e.g.</em> spacing=5</span></dd>

    <dt>width</dt>
    <dd><?php echo _("integer-based output width in pixels"); ?> <span class="api-example"><em>e.g.</em> width=400</span></dd>

    <dt>height</dt>
    <dd><?php echo _("integer-based output height in pixels; if height is not provided, it will be half the width"); ?> <span class="api-example"><em>e.g.</em> height=400</span></dd>

    <dt>output</dt>
    <dd><?php echo _("file format of the image or vector produced; options are png, jpg, tif, svg"); ?> <span class="api-example"><em>e.g.</em> output=png</span></dd>

    <dt>scalebar</dt>
    <dd><?php echo _("embed a scalebar in the lower right of the image"); ?> <span class="api-example"><em>e.g.</em> scalebar=true</span></dd>

    <dt>legend[x]</dt>
    <dd><?php echo _("URL-encode a title for an item in a legend, embedded in the upper right of the image. If you have a url or file parameter, use legend=true instead"); ?> <span class="api-example"><em>e.g.</em> legend[0]=Pardosa%20moesta or legend=true</span>
  </dl>
</div>