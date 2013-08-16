<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root.'/config/conf.php');
require_once($root.'/lib/mappr.class.php');
require_once($root.'/lib/session.class.php');
require_once($root.'/lib/citation.class.php');
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
<p><?php echo _("Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result."); ?></p>
</div>
<div class="header"><h2><?php echo _("Citing"); ?></h2></div>
<p><a href="http://creativecommons.org/publicdomain/"><img class="map-license" src="/public/images/publicdomain.gif" alt="Public Domain" width="40" height="40" /></a><?php echo _("All versions of SimpleMappr map data found on this website are in the Public Domain. You may use the maps in any manner, including modifying the content and design, electronic dissemination, and offset printing. The primary author, David P. Shorthouse renounces all financial claim to the maps and invites you to use them for personal, educational, and commercial purposes. No permission is needed to use SimpleMappr. Crediting the author is unnecessary. However, if you wish to cite the application, simply use the following."); ?></p>
<p>Shorthouse, David P. 2010. SimpleMappr, an online tool to produce publication-quality point maps. [Retrieved from http://www.simplemappr.net. Accessed <?php echo date("d F, Y"); ?>].</p>
<div class="ui-helper-clearfix">
<div id="recent-updates">
<div class="header"><h2><?php echo _("Recent Updates"); ?></h2></div>
<dl>
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
  $doi = ($citation['doi']) ? ' doi:<a href="http://doi.org/' . $citation['doi'] . '">' . $citation['doi'] . '</a>.' : "";
  $link = ($citation['link']) ? ' (<a href="' . $citation['link'] . '">link</a>)' : "";
  echo '<p class="citation">' . $citation['reference'] . $link . $doi .'</p>';
}
?>
<!--

<p class="citation">Haddad, Charles R. 2013. Taxonomic notes on the spider genus <em>Messapus</em> Simon, 1898 (Araneae, Corinnidae), with the description of the new genera <em>Copuetta</em> and <em>Wasaka</em> and the first cladistic analysis of Afrotropical Castianeirinae. <em>Zootaxa</em> 3688 (1): 001–079. doi:<a href="http://dx.doi.org/10.11646/zootaxa.3688.1.1">10.11646/zootaxa.3688.1.1</a>.</p>
<p class="citation">Haddad, Charles R. 2013. A revision of the ant-like sac spider genus <em>Apochinomma</em> Pavesi 1881 (Araneae: Corinnidae) in the Afrotropical Region. <em>Journal of Natural History</em> doi:<a href="http://dx.doi.org/10.1080/00222933.2013.791933">10.1080/00222933.2013.791933</a>.</p>
<p class="citation">Haddad, Charles R. 2013. A revision of the continental species of <em>Copa</em> Simon, 1885 (Araneae, Corinnidae) in the Afrotropical Region. <em>Zookeys</em> 276: 1-37. doi:<a href="http://dx.doi.org/10.3897/zookeys.276.4233">10.3897/zookeys.276.4233</a>.</p>
<p class="citation">Haddad, Charles R. 2012. A revision of the Afrotropical spider genus <em>Cambalida</em> Simon, 1909 (Araneae, Corinnidae) <em>Zookeys</em> 234: 67–119. doi:<a href="http://dx.doi.org/10.3897/zookeys.234.3417">10.3897/zookeys.234.3417</a>.</p>
<p class="citation">Haddad, Charles R. and Schalk vdM. Louw. 2012. A redescription of <em>Merenius alberti</em> Lessert, 1923 (Araneae: Corinnidae), with remarks on colour polymorphism and its relationship to ant models. <em>African Invertebrates</em> 53(2): 571–591. (<a href="http://www.africaninvertebrates.org.za/Haddad_Louw_2012_53_2_580.aspx">link</a>)</p>
<p class="citation">Haverkort-Yeh, Roxanne D., Catherine S. McFadden, Yehuda Benayahu, Michael Berumen, Anna Halász, and Robert J. Toonen. 2013. A taxonomic survey of Saudi Arabian Red Sea octocorals (Cnidaria: Alcyonacea). <em>Marine Biodiversity</em> doi:<a href="http://dx.doi.org/10.1007/s12526-013-0157-4">10.1007/s12526-013-0157-4</a>.</p>
<p class="citation">Hodkinson, Brendan P. and James C. Lendemer. 2012. Phylogeny and taxonomy of an enigmatic sterile lichen. <em>Systematic Botany</em> 37(4): 835-844. doi:<a href="http://dx.doi.org/10.1600/036364412X656536">10.1600/036364412X656536</a>.</p>
<p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">link</a>)</p>
<p class="citation">Locke, Michelle M. and Jeffrey H. Skevington. 2013. Revision of Nearctic <em>Dasysyrphus</em> Enderlein (Diptera: Syrphidae). <em>Zootaxa</em> 3660(1): 1-80. doi:<a href="http://dx.doi.org/10.11646/zootaxa.3660.1.1">10.11646/zootaxa.3660.1.1</a>.</p>
<p class="citation">Orozco, J. 2012. Monographic revision of the American genus <em>Euphoria</em> Burmeister, 1842 (Coleoptera: Scarabaeidae: Cetoniinae). <em>The Coleopterists Bulletin</em> 66(mo4): 1-182. doi:<a href="http://dx.doi.org/10.1649/0010-066X-66.mo4.1">10.1649/0010-066X-66.mo4.1</a>.</p>
<p class="citation">Qi, X., L. Xiao–Long, B. Yi, and X.–H. Wang. 2013. <em>Polypedilum (Tripodura) harteni</em> Andersen &amp; Mendes (Diptera: Chironomidae) newly recorded from China. <em>The Pan-Pacific Entomologist</em> 89(2): 73-78 doi:<a href="http://dx.doi.org/10.3956/2012-55.1">10.3956/2012-55.1</a>.</p>
<p class="citation">Rakotonirina, N., Rakouth, B. and Aaron P. Davis. 2012. A taxonomic revision of Madagascan <em>Gardenia</em> (Rubiaceae, Gardenieae). <em>Nordic Journal of Botany</em> doi:<a href="http://dx.doi.org/10.1111/j.1756-1051.2012.01155.x">10.1111/j.1756-1051.2012.01155.x</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2013. The taxonomy of <em>Hoya revoluta</em> (Apocynaceae, Asclepiadoideae). <em>Webbia: Journal of Plant Taxonomy and Geography</em> 68(1): 7-16. doi:<a href="http://dx.doi.org/10.1080/00837792.2013.802937">10.1080/00837792.2013.802937</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. <em>Hoya mappigera</em> (Apocynaceae, Asclepiadoideae), a new campanulate flowered species from Peninsular Malaysia and southern Thailand. <em>Feddes Repertorium</em>. doi:<a href="http://dx.doi.org/10.1002/fedr.201100019">10.1002/fedr.201100019</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. <em>Hoya somadeeae</em> sp. nov. (Apocynaceae, Asclepiadoideae) Thailand and lectotypification of <em>Hoya wrayi</em>. <em>Nordic Journal of Botany</em>, early view. doi:<a href="http://dx.doi.org/10.1111/j.1756-1051.2011.01400.x">10.1111/j.1756-1051.2011.01400.x</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. Taxonomic notes on the long-lost <em>Hoya burmanica</em> (Apocynaceae, Asclepiadoideae) from Myanmar. <em>Kew Bulletin</em>. 1-6. doi:<a href="http://dx.doi.org/10.1007/s12225-012-9377-1">10.1007/s12225-012-9377-1</a>.</p>
<p class="citation">Rodda, Michele and Nadhanielle Simonsson. 2011. <em>Hoya devogelii</em> (Apocynaceae Asclepiadoideae), a new species from kerangas heath forests in Sarawak, Borneo <em>Webbia</em> 66(1): 33-38. doi:<a href="http://dx.doi.org/10.1080/00837792.2011.10670882">10.1080/00837792.2011.10670882</a>.</p>
<p class="citation">Rodda, Michele and Nadhanielle Simonsson. 2011. <em>Hoya medinillifolia</em> (Apocynaceae Asclepiadoideae), a new species from lowland forests of Sarawak, Borneo <em>Webbia</em> 66(2): 149-154. doi:<a href="http://dx.doi.org/10.1080/10.1080/00837792.2011.10670893">10.1080/00837792.2011.10670893</a>.</p>
<p class="citation">Schlinger, E. I., J. P. Gillung and C. J. Borkent. 2013. New spider flies from the neotropical region (Diptera, Acroceridae) with a key to New World genera. <em>ZooKeys</em>. 270: 59-93. doi:<a href="http://dx.doi.org/10.3897/zookeys.270.4476">10.3897/zookeys.270.4476</a>.</p>
<p class="citation">Schuh, Randall T. 2012. Integrating specimen databases and revisionary systematics. <em>ZooKeys</em> 209: 255-267. doi:<a href="http://dx.doi.org/10.3897/zookeys.209.3288">10.3897/zookeys.209.3288</a>.</p>
<p class="citation">Scudder, G.G.E. and Michael D. Schwartz. 2012. Two new species of <em>Trigonotylus</em> (Hemiptera: Heteroptera: Miridae: Stenodemini) from western Canada and northwestern United States. <em>Zootaxa</em> 3174: 51-58. (<a href="http://www.mapress.com/zootaxa/2012/f/zt03174p058.pdf">PDF</a>, 1.4MB)</p>
<p class="citation">Sikes, Derek S. and Tonya Mousseau. 2013. Description of <em>Nicrophorus efferens</em>, new species, from Bougainville Island (Coleoptera, Silphidae, Nicrophorinae). <em>ZooKeys</em> 311: 83-93. doi:<a href="http://dx.doi.org/10.3897/zookeys.311.5141">10.3897/zookeys.311.5141</a>.</p>
<p class="citation">Skevington, J.H. and F.C. Thompson. 2012. Review of New World <em>Sericomyia</em> (Diptera, Syrphidae), including description of a new species. <em>The Canadian Entomologist</em> 144: 216-247. doi:<a href="http://dx.doi.org/10.4039/tce.2012.24">10.4039/tce.2012.24</a>.</p>
<p class="citation">Tessler, M. 2012. A monograph of <em>Hymenodon</em> (Orthodontiaceae). <em>The Bryologist</em> 115(4): 493-517. doi:<a href="http://dx.doi.org/10.1639/0007-2745-115.4.493">10.1639/0007-2745-115.4.493</a>.</p>
<p class="citation">Uhlig, Manfred and Jiří Janák. 2013. <em>Erichsonius (Sectophilonthus) dorsumsuis</em> sp. nov. from Eastern Cape and KwaZulu-Natal Provinces, South Africa (Coleoptera: Staphylinidae, Staphylininae). <em>Acta Entomologica Musei Nationalis Prague</em> 53(1): 209-218. (<a href="http://www.aemnp.eu/PDF/53_1/53_1_209.pdf">PDF</a>, 0.6MB)</p>
<p class="citation">Wesołowska, W. &amp; C.R. Haddad. 2013. New data on the jumping spiders of South Africa (Araneae: Salticidae). <em>African Invertebrates</em> 54(1): 177–240. (<a href="http://africaninvertebrates.org/ojs/index.php/AI/article/view/265">link</a>)</p>
<p class="citation">Wilkin, P., P. Suksathan, K. Keeratikiat, P. Van Welzen and J. Wiland-Szymańska. 2012. A new threatened endemic species from central and northeastern Thailand, <em>Dracaena jayniana</em> (Asparagaceae: tribe Nolinoideae). <em>Kew Bulletin</em> 67: 1-9. doi:<a href="http://dx.doi.org/10.1007/s12225-012-9412-2">10.1007/s12225-012-9412-2</a>.</p>
<p class="citation">Wyniger, Denise. 2011. Revision of the Nearctic genus <em>Coquillettia</em> Uhler with a transfer to the tribe Phylini, the description of 14 new species, a new synonymy, and the description of two new Nearctic genera <em>Leutiola</em> and <em>Ticua</em> and two new species (Heteroptera: Miridae: Phylinae). <em>Entomologica Americana</em> 117(3 &amp; 4): 134-211. doi:<a href="http://dx.doi.org/10.1664/11-RA-012.1">10.1664/11-RA-012.1</a>.</p>
<p class="citation">Zubov, Dmitry A. and Aaron P. Davis. 2012. <em>Galanthus panjutinii</em> sp. nov.: a new name for an invalidly published species of <em>Galanthus</em> (Amaryllidaceae) from the northern Colchis area of Western Transcaucasia. <em>Phytotaxa</em> 50: 55-63. (<a href="http://www.mapress.com/phytotaxa/content/2012/pt00050.htm">issue</a>)</p>
-->
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