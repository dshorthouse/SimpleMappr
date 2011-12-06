<?php
require_once('../lib/mapprservice.class.php');
require_once('../lib/mapprservice.usersession.class.php');
USERSESSION::select_language();
?>
<!-- api tab -->
<div id="map-api">
  <div id="general-api" class="panel ui-corner-all">
    <p><?php echo _("A simple, restful API may be used with Internet accessible, tab-separated text files with additional parameters outlined below."); ?></p>
  </div>
  <p><em>e.g.</em> http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?<br>file=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/demo.txt'); ?>&amp;<br>shape[0]=square&amp;size[0]=10&amp;color[0]=20,20,20&amp;<br>shape[1]=triangle&amp;size[1]=10&amp;color[1]=40,40,40&amp;<br>shape[2]=star&amp;size[2]=14&amp;color[2]=60,60,60&amp;<br>width=500&amp;height=300&amp;<br>bbox=-130,40,-60,50&amp;<br>layers=lakes,stateprovinces&amp;graticules=true&amp;projection=esri:102009&amp;legend=true&amp;<br>shade[places]=Alberta,USA[MT|WA]&amp;shade[title]=Selected Regions&amp;shade[color]=150,150,150</p>
  <p><strong><?php echo _("Produces"); ?></strong></p>
  <p><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/public/images/api.png" width="500" height="300" alt="<?php echo _("My Map"); ?>" /></p>

  <div class="header">
    <h2><?php echo _("Base URL"); ?></h2>
  </div>

  <div id="general-base" class="panel ui-corner-all">
    <p>http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?</p>
  </div>

  <div class="header">
    <h2><?php echo _("Parameters"); ?></h2>
  </div>

  <dl>
    <dt>ping</dt>
    <dd><?php echo sprintf(_("if %s is included, a JSON response will be produced in place of an image as: %s"), "ping=true", "{\"status\" : \"ok\"}"); ?></dd>
    
    <dt>file</dt>
    <dd><?php echo _("a URL-encoded, remote tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated points."); ?> <span class="api-example"><em>e.g.</em> file=<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/demo.txt"><?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/demo.txt'); ?></a></span></dd>
    
    <dt>georss</dt>
    <dd><?php echo _("a URL-encoded, remote GeoRSS feed. <strong>NOTE:</strong> If both <em>file</em> and <em>georss</em> are included, points in the GeoRSS feed are treated as a single, additional \"column\" for <em>shape[x]</em>, <em>size[x]</em>, <em>color[x]</em> below."); ?> <span class="api-example"><em>e.g.</em> georss=<a href="http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml"><?php echo urlencode('http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml'); ?></a></span></dd>

    <dt>point[x]</dt>
    <dd><?php echo _("single marker written as latitude,longitude"); ?> <span class="api-example"><em>e.g.</em> point[0]=45,-120</span></dd>

    <dt>shape[x]</dt>
    <dd><?php echo _("shape of marker for column x; options are circle, square, triangle, star, opencircle, opensquare, opentriangle, openstar"); ?> <span class="api-example"><em>e.g.</em> shape[0]=circle</span></dd>

    <dt>size[x]</dt>
    <dd><?php echo _("integer-based point size of marker in column x"); ?> <span class="api-example"><em>e.g.</em> size[1]=10</span></dd>

    <dt>color[x]</dt>
    <dd><?php echo _("comma-separated RGB colors for marker in column x"); ?> <span class="api-example"><em>e.g.</em> color[2]=255,0,0</span></dd>

    <dt>outlinecolor</dt>
    <dd><?php echo ("comma-separated RGB colors for halo around all solid markers"); ?> <span class="api-example"><em>e.g.</em> outlinecolor=40,40,40</span></dd>

    <dt>bbox</dt>
    <dd><?php echo sprintf(_("comma-separated bounding box in decimal degrees %s"), "(minx, miny, maxx, maxy)"); ?> <span class="api-example"><em>e.g.</em> bbox=-130,40,-60,50</span></dd>

    <dt>shade[places]</dt>
    <dd><?php echo _("comma-separated State, Province or Country names or the three-letter ISO country code with pipe-separated States or Provinces flanked by brackets"); ?> <span class="api-example"><em>e.g.</em> shade[places]=Alberta,USA[MT|WA]</span></dd>

    <dt>shade[title]</dt>
    <dd><?php echo _("the title for the shaded places"); ?> <span class="api-example"><em>e.g.</em> shade[title]=Occurrence</span></dd>

    <dt>shade[color]</dt>
    <dd><?php echo _("comma-separated RGB fill colors for shaded places"); ?> <span class="api-example"><em>e.g.</em> shade[color]=150,150,150</span></dd>

    <dt>layers</dt>
    <dd><?php echo _("comma-separated cultural or physical layers; options are relief, stateprovinces, lakes, rivers, placenames"); ?> <span class="api-example"><em>e.g.</em> layers=lakes,stateprovinces</span></dd>

    <dt>projection</dt>
    <dd><?php echo sprintf(_("the output projection in either EPSG or ESRI references. See %s for spatial references. Accepted projections are:"), "<a href=\"http://spatialreference.org/\">http://spatialreference.org/</a>"); ?> <?php foreach(MAPPR::$accepted_projections as $key => $value) { print $key . ' (=' . $value['name'] . '), '; } ?><span class="api-example"><em>e.g.</em> projection=esri:102009</span></dd>

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

    <dt>legend</dt>
    <dd><?php echo _("embed a legend in the upper right of the image"); ?> <span class="api-example"><em>e.g.</em> legend=true</span></dd>
  </dl>
</div>