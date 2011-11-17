<?php
require_once('../config/conf.php');
?>
<!-- about tab -->
<div id="map-about">
<div id="general-about" class="panel ui-corner-all">
<p><?php echo _("Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result."); ?></p>
</div>
<div class="header"><h2><?php echo _("Citing"); ?></h2></div>
<p>Shorthouse, David P. 2010. SimpleMappr, a web-enabled tool to produce publication-quality point maps. Retrieved from http://www.simplemappr.net. Accessed <?php echo date("Y-m-d"); ?>.</p>
<div class="header"><h2><?php echo _("Recent Updates"); ?></h2></div>
<dl>
<dt class="update"><?php echo _("November 16, 2011"); ?></dt><dd><?php echo _("Bug fixes: Scalebar showed tick measures in exponents (reported by GIS Unit Kew)."); ?></dd>
<dt class="update"><?php echo _("November 15, 2011"); ?></dt><dd><?php echo _("Improvements: added an autocomplete mechanism for country names in the Regions tab; refined drawing of border around map when legend present. Bug fixes: Added more Google Earth pushpins in the kml output."); ?></dd>
<dt class="update"><?php echo _("November 4, 2011"); ?></dt><dd><?php echo _("Improvements: a new look & feel for better use of screen real estate and to accommodate eventual internationalization."); ?></dd>
<dt class="update"><?php echo _("October 24, 2011"); ?></dt><dd><?php echo _("Bug fixes: corrected mechanism to draw map borders to accommodate rotation."); ?></dd>
<dt class="update"><?php echo _("October 23, 2011"); ?></dt><dd><?php echo _("Improvements: converted map rotation to a circular slider; loading a saved map with a rotation will indicate rotated angle."); ?></dd>
<dt class="update"><?php echo _("October 20, 2011"); ?></dt><dd><?php echo _("Improvements: added a Country label option; scale bar is now always present in preview but optional for download."); ?></dd>
<dt class="update"><?php echo _("October 19, 2011"); ?></dt><dd><?php echo _("Improvements: legend appearance in downloads and API. Bug fixes: proper rendering of Point Data/Regions layers after one or more were removed; improved behaviour of crop when map loaded."); ?></dd>
<dt class="update"><?php echo _("October 18, 2011"); ?></dt><dd><?php echo _("Improvements: dedicated label section in map preview settings; State/Province borders thickened to differentiate from rivers and lake outlines; lake fills made less stark to accommodate lake and river labels."); ?></dd>
<dt class="update"><?php echo _("October 6, 2011"); ?></dt><dd><?php echo _("Improvements: removed eps as an output format because it was raster-based; preview of shaded relief layers made faster."); ?></dd>
<dt class="update"><?php echo _("October 2, 2011"); ?></dt><dd><?php echo _("Bug fixes: incorrect expansion of Point Data/Regions layers after a saved map is loaded (reported by Chris Borkent)."); ?></dd>
<dt class="update"><?php echo _("October 1, 2011"); ?></dt><dd><?php echo _("Improvements: keyboard shortcuts for common actions (arrow keys pan when cursor is hovered over the map)."); ?></dd>
<!--
<dt class="update"><?php echo _("September 29, 2011"); ?></dt><dd><?php echo _("Improvements: crop selection restored after refreshing, zooming, panning, or selecting different layers or options."); ?></dd>
<dt class="update"><?php echo _("September 28, 2011"); ?></dt><dd><?php echo _("Improvements: editable corner coordinates for crop and improved accuracy."); ?></dd>
<dt class="update"><?php echo _("September 26, 2011"); ?></dt><dd><?php echo _("Improvements: responsiveness of application improved by accommodating browser caching."); ?></dd>
<dt class="update"><?php echo _("September 21, 2011"); ?></dt><dd><?php echo _("Improvements: added graticule options (requested by Jeff Skevington); cleaned presentation of download options. Bug fixes: production of KML files."); ?></dd>
<dt class="update"><?php echo _("August 2, 2011"); ?></dt><dd><?php echo _("Improvements: refined error-handling in coordinate recognition."); ?></dd>
<dt class="update"><?php echo _("July 5, 2011"); ?></dt><dd><?php echo _("Improvements: filtering My Maps by title."); ?></dd>
<dt class="update"><?php echo _("July 4, 2011"); ?></dt><dd><?php echo _("Improvements: fill bucket produces a colour selector and regions may be immediately filled by either clicking or click-dragging on the map; repeat actions adds another layer to the Regions tab; clear buttons added to each layer in the Point Data and Regions tabs (requested by Chris Borkent)."); ?></dd>
<dt class="update"><?php echo _("July 3, 2011"); ?></dt><dd><?php echo _("Bug fixes: removed State/Province line artifacts when map is reprojected (reported by Chris Borkent)."); ?></dd>
<dt class="update"><?php echo _("June 28, 2011"); ?></dt><dd><?php echo _("Improvements: layers on the Point Data or Regions tabs may be removed (requested by Chris Borkent)."); ?></dd>
<dt class="update"><?php echo _("June 27, 2011"); ?></dt><dd><?php echo _("Improvements: ISO Country codes and regions code table added to the Help tab (requested by API users)."); ?></dd>
<dt class="update"><?php echo _("June 26, 2011"); ?></dt><dd><?php echo _("Improvements: file names may be specified when downloading."); ?></dd>
-->
</dl>
<div class="header"><h2><?php echo _("In the Wild"); ?></h2></div>
<p class="citation">Carr, Christina May. 2011. Polychaete diversity and distribution patterns in Canadian marine waters. <em>Marine Biodiversity</em> Online first, doi:<a href="http://dx.doi.org/10.1007/s12526-011-0095-y">10.1007/s12526-011-0095-y</a></p>
<p class="citation">Carr, C.M., Hardy, S.M., Brown, T.M., Macdonald, T.A., Hebert, P.D.N. 2011. A Tri-Oceanic Perspective: DNA Barcoding Reveals Geographic Structure and Cryptic Diversity in Canadian Polychaetes. <em>PLoS ONE</em> 6(7): e22232. doi:<a href="http://dx.doi.org/10.1371/journal.pone.0022232">10.1371/journal.pone.0022232</a></p>
<p class="citation">Cuzepan, Gabriela. 2011. Diving beetles (Coleoptera: Dytiscidae) from the Transylvanian Society collection of The Natural History Museum of Sibiu (Romania). <em>Travaux du Muséum National d’Histoire Naturelle</em> 54(1): 69-87. doi:<a href="http://dx.doi.org/10.2478/v10191-011-0005-3">10.2478/v10191-011-0005-3</a></p>
<p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">permalink</a>)</p>
<div class="header"><h2><?php echo _("Code"); ?></h2></div>
<p><?php echo sprintf(_("The code behind SimpleMappr may be obtained at %s"), "<a href=\"https://github.com/dshorthouse/SimpleMappr\">https://github.com/dshorthouse/SimpleMappr</a>"); ?></p>
<div class="header"><h2><?php echo _("History"); ?></h2></div>
<p><?php echo _("The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History."); ?></p>
<div class="header"><h2><?php echo _("Acknowledgments"); ?></h2></div>
<p><?php echo sprintf(_("Underlying ArcView shapefiles were obtained from Natural Earth, %s and the mapping software used is MapServer, %s via PHP MapScript."), "<a href=\"http://www.naturalearthdata.com/\" target=\"_blank\">http://www.naturalearthdata.com/</a>", "<a href=\"http://mapserver.org\" target=\"_blank\">http://mapserver.org</a>"); ?></p>
</div>