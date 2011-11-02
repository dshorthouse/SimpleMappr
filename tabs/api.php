<?php
require_once('../lib/mapprservice.class.php');
?>
<!-- api tab -->
<div id="map-api">
    <div id="general-api" class="panel ui-corner-all">
        <p><?php echo _("A simple, restful API may be used with Internet accessible, tab-separated text files with additional parameters outlined below."); ?></p>
    </div>
    <p><em>e.g.</em> http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?<br>file=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/demo.txt'); ?>&amp;<br>shape[0]=square&amp;size[0]=10&amp;color[0]=20,20,20&amp;<br>shape[1]=triangle&amp;size[1]=10&amp;color[1]=40,40,40&amp;<br>shape[2]=star&amp;size[2]=14&amp;color[2]=60,60,60&amp;<br>width=500&amp;height=300&amp;<br>bbox=-130,40,-60,50&amp;<br>layers=lakes,stateprovinces&amp;graticules=true&amp;projection=esri:102009&amp;legend=true&amp;<br>shade[places]=Alberta,USA[MT|WA]&amp;shade[title]=Selected Regions&amp;shade[color]=150,150,150</p>
    <p><strong>Produces:</strong></p>
    <p><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?file=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/demo.txt'); ?>&amp;shape[0]=square&amp;size[0]=10&amp;color[0]=20,20,20&amp;shape[1]=triangle&amp;size[1]=10&amp;color[1]=40,40,40&amp;shape[2]=star&amp;size[2]=14&amp;color[2]=60,60,60&amp;width=500&amp;height=300&amp;bbox=-130,40,-60,50&amp;layers=lakes,stateprovinces&amp;graticules=true&amp;projection=esri:102009&amp;legend=true&amp;shade[places]=Alberta,USA[MT|WA]&amp;shade[title]=Selected%20Regions&amp;shade[color]=150,150,150" alt="My Map" /></p>

    <div class="header">
      <h2>Base URL</h2>
    </div>

    <div id="general-base" class="panel ui-corner-all">
        <p>http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/?</p>
    </div>

    <div class="header">
      <h2>Parameters</h2>
    </div>
    <dl>
        <dt>ping</dt>
        <dd>if ping=true is included, a JSON response will be produced in place of an image as: {"status" : "ok"}</dd>
        
        <dt>file</dt>
        <dd>a URL-encoded, remote tab-separated text file the columns within which are treated as groups of points; the first row used for an optional legend; rows are comma- or space-separated points. <em>e.g. file=<a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/api/demo.txt"><?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/demo.txt'); ?></a></em></dd>
        
        <dt>georss</dt>
        <dd>a URL-encoded, remote GeoRSS feed. <strong>NOTE:</strong> If both <em>file</em> and <em>georss</em> are included, points in the GeoRSS feed are treated as a single, additional "column" for <em>shape[x]</em>, <em>size[x]</em>, <em>color[x]</em> below. <em>e.g. georss=<a href="http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml"><?php echo urlencode('http://earthquake.usgs.gov/eqcenter/recenteqsww/catalogs/eqs7day-M5.xml'); ?></a></em></dd>

        <dt>shape[x]</dt>
        <dd>shape of marker for column x; options are circle, square, triangle, star, opencircle, opensquare, opentriangle, openstar <em>e.g. shape[0]=circle</em></dd>

        <dt>size[x]</dt>
        <dd>integer-based point size of marker in column x <em>e.g. size[1]=10</em></dd>

        <dt>color[x]</dt>
        <dd>comma-separated RGB colors for marker in column x <em>e.g. color[2]=255,0,0</em></dd>

        <dt>outlinecolor</dt>
        <dd>comma-separated RGB colors for halo around all solid markers <em>e.g. outlinecolor=40,40,40</em></dd>

        <dt>bbox</dt>
        <dd>comma-separated bounding box in decimal degrees (minx, miny, maxx, maxy) <em>e.g. bbox=-130,40,-60,50</em></dd>

        <dt>shade[places]</dt>
        <dd>comma-separated State, Province or Country names or the three-letter ISO country code with pipe-separated States or Provinces flanked by brackets <em>e.g. shade[places]=Alberta,USA[MT|WA]</em></dd>

        <dt>shade[title]</dt>
        <dd>the title for the shaded places <em>e.g. shade[title]=Occurrence</em></dd>

        <dt>shade[color]</dt>
        <dd>comma-separated RGB fill colors for shaded places <em>e.g. shade[color]=150,150,150</em></dd>

        <dt>layers</dt>
        <dd>comma-separated cultural or physical layers; options are relief, stateprovinces, lakes, rivers, placenames <em>e.g. layers=lakes,stateprovinces</em></dd>

        <dt>projection</dt>
        <dd>the output projection in either EPSG or ESRI references. See <a href="http://spatialreference.org/">http://spatialreference.org/</a> for spatial references. Accepted projections are: <?php foreach(MAPPR::$accepted_projections as $key => $value) { print $key . ' (=' . $value['name'] . '), '; } ?><em>e.g. projection=esri:102009</em></dd>

        <dt>graticules</dt>
        <dd>display the graticules <em>e.g. graticules=true</em></dd>

        <dt>spacing</dt>
        <dd>display the graticules with defined spacing in degrees <em>e.g. spacing=5</em></dd>

        <dt>width</dt>
        <dd>integer-based output width in pixels <em>e.g. width=400</em></dd>

        <dt>height</dt>
        <dd>integer-based output height in pixels <em>e.g. height=400</em></dd>

        <dt>output</dt>
        <dd>file format of the image or vector produced; options are png, jpg, tif, svg <em>e.g. output=png</em></dd>

        <dt>scalebar</dt>
        <dd>embed a scalebar in the lower right of the image <em>e.g. scalebar=true</em></dd>

        <dt>legend</dt>
        <dd>embed a legend in the upper right of the image <em>e.g. legend=true</em></dd>
    </dl>
</div>