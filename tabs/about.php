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
<p class="update"><strong>October 19, 2011</strong><span>Fixed bug in removal of layers. Improved legend appearance in downloads.</span></p>
<p class="update"><strong>October 18, 2011</strong><span>Made a dedicated label section in map preview settings. State/Province borders were thickened to differentiate from rivers and lake outlines. Lake fills were made less stark to accommodate new lake and river label options.</span></p>
<p class="update"><strong>October 6, 2011</strong><span>Removed eps as an output format because it was raster-based whereas tiff is equally good. Increased performance by allowing for browser caching; revisits should re-render the application in near sub-second times. Previewing shaded relief layers should be faster because it now produces images with less depth of colour; downloads continue to have quality outputs.</span></p>
<p class="update"><strong>October 2, 2011</strong><span>Fixed a bug that prevented multiple layers or regions from properly expanding after a saved map is loaded.</span></p>
<p class="update"><strong>October 1, 2011</strong><span>Added keyboard shortcuts for common actions. These are shown in the tooltips. Arrow keys also pan the map when your cursor is hovered over the map. Performance was optimized.</span></p>
<p class="update"><strong>September 29, 2011</strong><span>Crop corner coordinates are now stored in a cookie such that a crop selection can be restored by clicking the crop icon once again after refreshing, zooming, panning, or selecting different layers or options. However, changing the projection clears these stored crop coordinates so the crop area will need to be redrawn.</span></p>
<p class="update"><strong>September 28, 2011</strong><span>Added editable corner coordinates for crop and improved its accuracy.</span></p>
<p class="update"><strong>September 26, 2011</strong><span>Responsiveness was improved by replacing map imagery when options are adjusted rather than replacing whole segments of HTML.</span></p>
<p class="update"><strong>September 21, 2011</strong><span>Added graticule options. Fixed production of KML files. Cleaned presentation of download options.</span></p>
<p class="update"><strong>August 2, 2011</strong><span>Refined error-handling with coordinate recognition.</span></p>
<p class="update"><strong>July 5, 2011</strong><span>Added the ability to filter your My Maps list by title.</span></p>
<p class="update"><strong>July 4, 2011</strong><span>The fill bucket in the map toolbar now produces a colour selector and regions may be immediately filled by either clicking or click-dragging on the map. Repeating this process adds another layer to the Regions tab. Clear buttons were added to each layer in the Point Data and Regions tabs.</span></p>
<p class="update"><strong>July 3, 2011</strong><span>State/Province line artifacts are not shown when the map is reprojected.</span></p>
<p class="update"><strong>June 28, 2011</strong><span>Additional layers on the Point Data or Regions tabs may be removed.</span></p>
<p class="update"><strong>June 27, 2011</strong><span>An ISO Country codes and regions code table was added to the Help tab.</span></p>
<p class="update"><strong>June 26, 2011</strong><span>File names may be specified when downloading maps.</span></p>
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