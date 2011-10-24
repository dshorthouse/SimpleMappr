<?php
require_once('../config/conf.php');
?>
<!-- about tab -->
<div id="map-about">
<div id="general-about" class="panel ui-corner-all">
<p>Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result.</p>
</div>
<div class="header"><h2>Citing</h2></div>
<p>Shorthouse, David P. 2010. SimpleMappr, a web-enabled tool to produce publication-quality point maps. Retrieved from http://www.simplemappr.net. Accessed <?php echo date("Y-m-d"); ?>.</p>
<div class="header"><h2>Recent Updates</h2></div>
<p class="update"><strong>October 24, 2011</strong><span>Bug fixes: corrected mechanism to draw map borders to accommodate rotation.</span></p>
<p class="update"><strong>October 23, 2011</strong><span>Improvements: converted map rotation to a circular slider; loading a saved map with a rotation will indicate rotated angle.</span></p>
<p class="update"><strong>October 20, 2011</strong><span>Improvements: added a Country label option; scale bar is now always present in preview but optional for download.</span></p>
<p class="update"><strong>October 19, 2011</strong><span>Improvements: legend appearance in downloads and API. Bug fixes: proper rendering of Point Data/Regions layers after one or more were removed; improved behaviour of crop when map loaded.</span></p>
<p class="update"><strong>October 18, 2011</strong><span>Improvements: dedicated label section in map preview settings; State/Province borders thickened to differentiate from rivers and lake outlines; lake fills made less stark to accommodate lake and river labels.</span></p>
<p class="update"><strong>October 6, 2011</strong><span>Improvements: removed eps as an output format because it was raster-based; preview of shaded relief layers made faster.</span></p>
<p class="update"><strong>October 2, 2011</strong><span>Bug fixes: incorrect expansion of Point Data/Regions layers after a saved map is loaded (reported by Chris Borkent).</span></p>
<p class="update"><strong>October 1, 2011</strong><span>Improvements: keyboard shortcuts for common actions (arrow keys pan when cursor is hovered over the map).</span></p>
<p class="update"><strong>September 29, 2011</strong><span>Improvements: crop selection restored after refreshing, zooming, panning, or selecting different layers or options.</span></p>
<p class="update"><strong>September 28, 2011</strong><span>Improvements: editable corner coordinates for crop and improved accuracy.</span></p>
<p class="update"><strong>September 26, 2011</strong><span>Improvements: responsiveness of application improved by accommodating browser caching.</span></p>
<p class="update"><strong>September 21, 2011</strong><span>Improvements: added graticule options (requested by Jeff Skevington); cleaned presentation of download options. Bug fixes: production of KML files.</span></p>
<p class="update"><strong>August 2, 2011</strong><span>Improvements: refined error-handling in coordinate recognition.</span></p>
<p class="update"><strong>July 5, 2011</strong><span>Improvements: filtering My Maps by title.</span></p>
<p class="update"><strong>July 4, 2011</strong><span>Improvements: fill bucket produces a colour selector and regions may be immediately filled by either clicking or click-dragging on the map; repeat actions adds another layer to the Regions tab; clear buttons added to each layer in the Point Data and Regions tabs (requested by Chris Borkent).</span></p>
<p class="update"><strong>July 3, 2011</strong><span>Bug fixes: removed State/Province line artifacts when map is reprojected (reported by Chris Borkent).</span></p>
<p class="update"><strong>June 28, 2011</strong><span>Improvements: layers on the Point Data or Regions tabs may be removed (requested by Chris Borkent).</span></p>
<p class="update"><strong>June 27, 2011</strong><span>Improvements: ISO Country codes and regions code table added to the Help tab (requested by API users).</span></p>
<p class="update"><strong>June 26, 2011</strong><span>Improvements: file names may be specified when downloading.</span></p>
<div class="header"><h2>In the Wild</h2></div>
<p class="citation">Carr, Christina May. 2011. Polychaete diversity and distribution patterns in Canadian marine waters. <em>Marine Biodiversity</em> Online first, doi:<a href="http://dx.doi.org/10.1007/s12526-011-0095-y">10.1007/s12526-011-0095-y</a></p>
<p class="citation">Carr, C.M., Hardy, S.M., Brown, T.M., Macdonald, T.A., Hebert, P.D.N. 2011. A Tri-Oceanic Perspective: DNA Barcoding Reveals Geographic Structure and Cryptic Diversity in Canadian Polychaetes. <em>PLoS ONE</em> 6(7): e22232. doi:<a href="http://dx.doi.org/10.1371/journal.pone.0022232">10.1371/journal.pone.0022232</a></p>
<p class="citation">Cuzepan, Gabriela. 2011. Diving beetles (Coleoptera: Dytiscidae) from the Transylvanian Society collection of The Natural History Museum of Sibiu (Romania). <em>Travaux du Muséum National d’Histoire Naturelle</em> 54(1): 69-87. doi:<a href="http://dx.doi.org/10.2478/v10191-011-0005-3">10.2478/v10191-011-0005-3</a></p>
<p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">permalink</a>)</p>
<div class="header"><h2>Code</h2></div>
<p>The code behind SimpleMappr may be obtained at <a href="https://github.com/dshorthouse/SimpleMappr">https://github.com/dshorthouse/SimpleMappr</a>.</p>
<div class="header"><h2>History</h2></div>
<p>The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History.</p>
<div class="header"><h2>Acknowledgments</h2></div>
<p>Underlying ArcView shapefiles were obtained from Natural Earth, <a href="http://www.naturalearthdata.com/" target="_blank">http://www.naturalearthdata.com/</a> and the mapping software used is MapServer, <a href="http://mapserver.org" target="_blank">http://mapserver.org</a> via PHP MapScript.</p>
</div>