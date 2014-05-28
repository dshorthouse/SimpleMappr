<?php
namespace SimpleMappr;

Session::select_locale();
?>
<!-- about tab -->
<style type="text/css">
#map-about p,#map-about dl{font-size:0.75em;}
#map-about p.citation{text-indent:-2em;padding-left:2em;}
#map-about dt.update{font-weight:bold;}
#map-about dd{margin:0 0 10px 25px;}
#recent-updates{float:left;width:65%;}
#live-updates{margin-left:65%;width:350px;padding:0.5em;}
#live-updates .header{width:85%;}
#twitter_div{margin-top:1em;}
.twtr-tweet-text{font-size:1.5em;}
.map-license{float:left;margin:0 5px 5px 0;border:0px;}
</style>
<div id="map-about">
<div id="general-about" class="panel ui-corner-all">
<p><?php echo _("Create free point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result."); ?></p>
</div>
<div class="header"><h2><?php echo _("Citing"); ?></h2></div>
<p><a href="http://creativecommons.org/publicdomain/"><img class="map-license" src="/public/images/publicdomain.gif" alt="Public Domain" width="40" height="40" /></a><?php echo _("All versions of SimpleMappr map data found on this website are in the Public Domain. You may use the maps in any manner, including modifying the content and design, electronic dissemination, and offset printing. The primary author, David P. Shorthouse renounces all financial claim to the maps and invites you to use them for personal, educational, and commercial purposes. No permission is needed to use SimpleMappr. Crediting the author is unnecessary. However, if you wish to cite the application, simply use the following."); ?></p>
<p>Shorthouse, David P. 2010. SimpleMappr, an online tool to produce publication-quality point maps. [Retrieved from http://www.simplemappr.net. Accessed <?php echo date("d F, Y"); ?>].</p>
<div class="ui-helper-clearfix">
<div id="recent-updates">
<div class="header"><h2><?php echo _("Recent Updates"); ?></h2></div>
<dl>
<dt class="update"><?php echo _("January 13, 2014"); ?></dt><dd><?php echo _("Improvements: Created a Web Map Service (WMS), which can be used by client applications like QGIS."); ?></dd>
<dt class="update"><?php echo _("October 29, 2013"); ?></dt><dd><?php echo _("Improvements: Upgraded underlying software. Added borders around biodiversity hotspot layer and option for labels. Requested by Torsten Dikow."); ?></dd>
<dt class="update"><?php echo _("June 25, 2013"); ?></dt><dd><?php echo _("Bug fixes: Point Data layers incorrectly loaded from saved map when there are blank layers followed by layers with data. Reported by Chandra Venables."); ?></dd>
<dt class="update"><?php echo _("May 19, 2013"); ?></dt><dd><?php echo _("Improvements: Added an option for a greyscale ocean layer and asterisk as a marker option."); ?></dd>
<dt class="update"><?php echo _("May 9, 2013"); ?></dt><dd><?php echo _("Improvements: Added 1 degree option for spacing of graticules. Requested by Michael Tessler."); ?></dd>
<dt class="update"><?php echo _("April 12, 2013"); ?></dt><dd><?php echo _("Improvements: Added the ability to specify the longitude of natural origin in Lambert projections. Requested by Tim Dickinson."); ?></dd>
<dt class="update"><?php echo _("February 27, 2013"); ?></dt><dd><?php echo _("Bug fixes: Relief layers had artifacts under polar projections. Reported by Derek Sikes."); ?></dd>
<dt class="update"><?php echo _("February 18, 2013"); ?></dt><dd><?php echo _("Improvements: Embedded map will appear cropped when saved as such. Requested by Steven Bachman."); ?></dd>
<dt class="update"><?php echo _("January 23, 2013"); ?></dt><dd><?php echo _("Improvements: Made line thickness in download proportional to user selected dimensions of output. Requested by Kevin M. Pfeiffer."); ?></dd>
<dt class="update"><?php echo _("October 16, 2012"); ?></dt><dd><?php echo _("Improvements: Enhanced the API to allow POST of multiple points."); ?></dd>
<dt class="update"><?php echo _("October 14, 2012"); ?></dt><dd><?php echo _("Improvements: Enhanced the API to allow sending of tab-delimited files."); ?></dd>
<dt class="update"><?php echo _("August 16, 2012"); ?></dt><dd><?php echo _("Improvements: Adjusted URLs with tab clicks to preserve use of back button."); ?></dd>
<dt class="update"><?php echo _("July 22, 2012"); ?></dt><dd><?php echo _("Bug fixes: Region Code lists can be filtered without causing the browser to crash."); ?></dd>
<dt class="update"><?php echo _("July 2, 2012"); ?></dt><dd><?php echo _("Improvements: Added svg as an embedded output format."); ?></dd>
<dt class="update"><?php echo _("July 1, 2012"); ?></dt><dd><?php echo _("Improvements: Added sort to columns in My Maps."); ?></dd>
<dt class="update"><?php echo _("June 14, 2012"); ?></dt><dd><?php echo _("Bug fixes: Download when legend selected in absence of regions or coordinates resulted in error thrown."); ?></dd>
</dl>
</div>
<div id="live-updates">
<div class="header"><h2><?php echo _("Live Updates"); ?></h2></div>
<div id="twitter_div"><a class="twitter-timeline" href="https://twitter.com/SimpleMappr" data-widget-id="325778519898603520">Tweets by @SimpleMappr</a></div>
</div>
</div>
<div class="header"><h2><?php echo _("In the Wild"); ?></h2></div>
<?php
$citations = new Citation();
foreach($citations->get_citations() as $citation) {
    $doi = ($citation->doi) ? ' doi:<a href="http://doi.org/' . $citation->doi . '">' . $citation->doi . '</a>.' : "";
    $link = ($citation->link) ? ' (<a href="' . $citation->link . '">link</a>)' : "";
    echo '<p class="citation">' . $citation->reference . $link . $doi .'</p>';
}
?>
<div class="header"><h2><?php echo _("Applications"); ?></h2></div>
<h3>Microsoft Excel</h3>
<p class="citation">Brown, Brian. V. 2013. Automating the "Material examined" section of
taxonomic papers to speed up species descriptions. <em>Zootaxa</em> 3683(3): 297. doi: <a href="http://dx.doi.org/10.11646/zootaxa.3683.3.8">10.11646/zootaxa.3683.3.8</a>, <a href="http://phorid.net/software/">http://phorid.net/software/</a>.</p>
<h3>Ruby 1.9.3</h3>
<p class="citation">SimpleMappr <a href="https://github.com/dshorthouse/SimpleMappr/wiki/Client-Example:-Ruby-1.9.3">wiki</a>.</p>
<div class="header"><h2><?php echo _("API Usage"); ?></h2></div>
<p><?php echo sprintf(_("The SimpleMappr API is used by The Missouri Botanical Garden's %s and The Encyclopedia of Life's %s as a custom %s module."), "<a href=\"http://www.tropicos.org/\">Tropicos</a>", "<a href=\"http://syrphidae.lifedesks.org/pages/24837\">LifeDesks</a>", "<a href=\"https://github.com/LifeDesks/LifeDesksExpert/tree/master/sites/all/modules/simplemappr\">Drupal</a>"); ?></p>
<div class="header"><h2><?php echo _("Code"); ?></h2></div>
<p><?php echo sprintf(_("The code behind SimpleMappr may be obtained at %s"), "<a href=\"https://github.com/dshorthouse/SimpleMappr\">https://github.com/dshorthouse/SimpleMappr</a>"); ?></p>
<div class="header"><h2><?php echo _("History"); ?></h2></div>
<p><?php echo _("The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History."); ?></p>
<div class="header"><h2><?php echo _("Acknowledgments"); ?></h2></div>
<p><?php echo sprintf(_("Underlying ArcView shapefiles were obtained from Natural Earth, %s and the mapping software used is MapServer, %s via PHP MapScript. Biodiversity Hotspot data were obtained from %s."), "<a href=\"http://www.naturalearthdata.com/\" target=\"_blank\">http://www.naturalearthdata.com/</a>", "<a href=\"http://mapserver.org\" target=\"_blank\">http://mapserver.org</a>", "<a href=\"http://www.conservation.org/where/priority_areas/hotspots/Pages/hotspots_main.aspx\" target=\"_blank\">Conservation International</a>"); ?></p>
</div>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>