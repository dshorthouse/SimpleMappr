<?php
require_once('../config/conf.php');
require_once('../lib/mapprservice.usersession.class.php');
USERSESSION::select_locale();
?>
<!-- about tab -->
<style type="text/css">
#map-about p,#map-about dl{font-size:0.75em;}
#map-about p.citation{text-indent:-2em;padding-left:2em;}
#map-about dt.update{font-weight:bold;}
#map-about dd{margin:0 0 10px 25px;}
#recent-updates{float:left;width:65%;}
#live-updates{margin-left:65%;width:300px;padding:0.5em;}
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
<p>Shorthouse, David P. 2010. SimpleMappr, an online tool to produce publication-quality point maps. [Retrieved from http://www.simplemappr.net. Accessed <?php echo date("d F, y"); ?>].</p>
<div class="ui-helper-clearfix">
<div id="recent-updates">
<div class="header"><h2><?php echo _("Recent Updates"); ?></h2></div>
<dl>
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
<div id="twitter_div"></div>
</div>
</div>
<div class="header"><h2><?php echo _("In the Wild"); ?></h2></div>
<p class="citation">Alves, V.R., R.A. de Freitas, F.L. Santos, A.F.J. de Oliveira, T.V. Barrett, and P.H.F. Shimabukuro. 2012. Sand flies (Diptera, Psychodidae, Phlebotominae) from Central Amazonia and four new records for the Amazonas state, Brazil. Revista Brasileira de Entomologia, (ahead), 0-0. doi:<a href="http://dx.doi.org/10.1590/S0085-56262012005000020">10.1590/S0085-56262012005000020</a>.</p>
<p class="citation">Borkent, Christopher J. and Terry A. Wheeler. 2012. Systematics and phylogeny of <em>Leptomorphus</em> Curtis (Diptera: Mycetophilidae). <em>Zootaxa</em> 3549: 1–117. <a href="http://www.mapress.com/zootaxa/list/2012/3549.html">permalink</a>.</p>
<p class="citation">Borrero, Francisco J. and Abraham S.H. Breure. 2011. The Amphibulimidae (Mollusca: Gastropoda: Orthalicoidea) from Colombia and adjacent areas. <em>Zootaxa</em> 3054: 1-59. <a href="http://www.mapress.com/zootaxa/list/2011/3054.html">permalink</a>.
<p class="citation">Breure, Abraham and Corey Whisson. 2012. Annotated type catalogue of <em>Bothriembryon</em> (Mollusca, Gastropoda, Orthalicoidea) in Australian museums, with a compilation of types in other museums. <em>ZooKeys</em> 194: 41-80. doi:<a href="http://dx.doi.org/10.3897/zookeys.194.2721">10.3897/zookeys.194.2721</a></p>
<p class="citation">Brothers, Denis J. 2012. The new genus <em>Ancistrotilla</em> n. gen., with new species from Vanuatu and New Caledonia (Hymenoptera, Mutillidae). <em>Zoosystema</em> 34(2): 223-251. doi:<a href="http://dx.doi.org/10.5252/z2012n2a2">10.5252/z2012n2a2</a></p>
<p class="citation">Caners, Richard T. 2013. Disjunct occurrence of <em>Harpanthus drummondii</em> (Taylor) Grolle (Geocalycaceae, Jungermanniopsida) in the boreal forest of West-Central Canada <em>Evansia</em> 30(1):24-30. doi:<a href="http://dx.doi.org/10.1639/079.030.0104">10.1639/079.030.0104</a>.</p>
<p class="citation">Carr, Christina May. 2011. Polychaete diversity and distribution patterns in Canadian marine waters. <em>Marine Biodiversity</em> Online first, doi:<a href="http://dx.doi.org/10.1007/s12526-011-0095-y">10.1007/s12526-011-0095-y</a></p>
<p class="citation">Carr, C.M., Hardy, S.M., Brown, T.M., Macdonald, T.A., Hebert, P.D.N. 2011. A Tri-Oceanic Perspective: DNA Barcoding Reveals Geographic Structure and Cryptic Diversity in Canadian Polychaetes. <em>PLoS ONE</em> 6(7): e22232. doi:<a href="http://dx.doi.org/10.1371/journal.pone.0022232">10.1371/journal.pone.0022232</a></p>
<p class="citation">Cuzepan, Gabriela. 2011. Diving beetles (Coleoptera: Dytiscidae) from the Transylvanian Society collection of The Natural History Museum of Sibiu (Romania). <em>Travaux du Muséum National d’Histoire Naturelle</em> 54(1): 69-87. doi:<a href="http://dx.doi.org/10.2478/v10191-011-0005-3">10.2478/v10191-011-0005-3</a></p>
<p class="citation">Floden, A. 2012. Notes on two rare <em>Solidago</em> (Asteraceae) in Tennessee: <em>S. arenicola</em> and <em>S. simplex</em>. <em>Phytoneuron</em> 2012-63: 1–4. (<a href="http://www.phytoneuron.net/PhytoN-Solidagoarenicola.pdf">PDF</a>, 52kb)</p>
<p class="citation">Gilligan, Todd M. and Donald J. Wright. 2013. The type species of <em>Eucosma</em> Hübner (Lepidoptera: Tortricidae: Eucosmini) <em>Zootaxa</em> 3630(3): 489–504. doi:<a href="http://dx.doi.org/10.11646/zootaxa.3630.3.5">10.11646/zootaxa.3630.3.5</a>.</p>
<p class="citation">Haddad, Charles R. 2013. A revision of the continental species of <em>Copa</em> Simon, 1885 (Araneae, Corinnidae) in the Afrotropical Region <em>Zookeys</em> 276: 1-37. doi:<a href="http://dx.doi.org/10.3897/zookeys.276.4233">10.3897/zookeys.276.4233</a>.</p>
<p class="citation">Haddad, Charles R. 2012. A revision of the Afrotropical spider genus <em>Cambalida</em> Simon, 1909 (Araneae, Corinnidae) <em>Zookeys</em> 234: 67–119. doi:<a href="http://dx.doi.org/10.3897/zookeys.234.3417">10.3897/zookeys.234.3417</a>.</p>
<p class="citation">Haddad, Charles R. and Schalk vdM. Louw. 2012. A redescription of <em>Merenius alberti</em> Lessert, 1923 (Araneae: Corinnidae), with remarks on colour polymorphism and its relationship to ant models. <em>African Invertebrates</em> 53(2): 571–591. <a href="http://www.africaninvertebrates.org.za/Haddad_Louw_2012_53_2_580.aspx">link</a></p>
<p class="citation">Hodkinson, Brendan P. and James C. Lendemer. 2012. Phylogeny and taxonomy of an enigmatic sterile lichen. <em>Systematic Botany</em> 37(4): 835-844. doi:<a href="http://dx.doi.org/10.1600/036364412X656536">10.1600/036364412X656536</a></p>
<p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">permalink</a>)</p>
<p class="citation">Orozco, J. 2012. Monographic revision of the American genus <em>Euphoria</em> Burmeister, 1842 (Coleoptera: Scarabaeidae: Cetoniinae). <em>The Coleopterists Bulletin</em> 66(mo4): 1-182. doi:<a href="http://dx.doi.org/10.1649/0010-066X-66.mo4.1">10.1649/0010-066X-66.mo4.1</a>.</p>
<p class="citation">Rakotonirina, N., Rakouth, B. and Aaron P. Davis. 2012. A taxonomic revision of Madagascan <em>Gardenia</em> (Rubiaceae, Gardenieae). <em>Nordic Journal of Botany</em> doi:<a href="http://dx.doi.org/10.1111/j.1756-1051.2012.01155.x">10.1111/j.1756-1051.2012.01155.x</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. <em>Hoya mappigera</em> (Apocynaceae, Asclepiadoideae), a new campanulate flowered species from Peninsular Malaysia and southern Thailand. <em>Feddes Repertorium</em>. doi:<a href="http://dx.doi.org/10.1002/fedr.201100019">10.1002/fedr.201100019</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. <em>Hoya somadeeae</em> sp. nov. (Apocynaceae, Asclepiadoideae) Thailand and lectotypification of <em>Hoya wrayi</em>. <em>Nordic Journal of Botany</em>, early view. doi:<a href="http://dx.doi.org/10.1111/j.1756-1051.2011.01400.x">10.1111/j.1756-1051.2011.01400.x</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. Taxonomic notes on the long-lost <em>Hoya burmanica</em> (Apocynaceae, Asclepiadoideae) from Myanmar. <em>Kew Bulletin</em>. 1-6. doi:<a href="http://dx.doi.org/10.1007/s12225-012-9377-1">10.1007/s12225-012-9377-1</a>.</p>
<p class="citation">Schlinger, E. I., J. P. Gillung and C. J. Borkent. 2013. New spider flies from the neotropical region (Diptera, Acroceridae) with a key to New World genera. <em>ZooKeys</em>. 270: 59-93. doi:<a href="http://dx.doi.org/10.3897/zookeys.270.4476">10.3897/zookeys.270.4476</a>.</p>
<p class="citation">Schuh, Randall T. 2012. Integrating specimen databases and revisionary systematics. <em>ZooKeys</em> 209: 255-267. doi:<a href="http://dx.doi.org/10.3897/zookeys.209.3288">10.3897/zookeys.209.3288</a>.</p>
<p class="citation">Scudder, G.G.E. and Michael D. Schwartz. 2012. Two new species of <em>Trigonotylus</em> (Hemiptera: Heteroptera: Miridae: Stenodemini) from western Canada and northwestern United States. <em>Zootaxa</em> 3174: 51-58. (<a href="http://www.mapress.com/zootaxa/2012/f/zt03174p058.pdf">PDF</a>, 1.4MB)</p>
<p class="citation">Skevington, J.H. and F.C. Thompson. 2012. Review of New World <em>Sericomyia</em> (Diptera, Syrphidae), including description of a new species. <em>The Canadian Entomologist</em> 144: 216-247. doi:<a href="http://dx.doi.org/10.4039/tce.2012.24">10.4039/tce.2012.24</a>.</p>
<p class="citation">Soto, Eduardo M. and Martín J. Ramírez. 2012. Revision and phylogenetic analysis of the spider genus <em>Philisca</em> Simon (Araneae: Anyphaenidae, Amaurobioidinae) <em>Zootaxa</em> 3443: 1–65. (<a href="http://www.mapress.com/zootaxa/list/2012/3443.html">issue</a>)</p>
<p class="citation">Tessler, M. 2012. A monograph of <em>Hymenodon</em> (Orthodontiaceae). <em>The Bryologist</em> 115(4): 493-517. doi:<a href="http://dx.doi.org/10.1639/0007-2745-115.4.493">10.1639/0007-2745-115.4.493</a>.</p>
<p class="citation">Wilkin, P., P. Suksathan, K. Keeratikiat, P. Van Welzen and J. Wiland-Szymańska. 2012. A new threatened endemic species from central and northeastern Thailand, <em>Dracaena jayniana</em> (Asparagaceae: tribe Nolinoideae). <em>Kew Bulletin</em> 67: 1-9. doi:<a href="http://dx.doi.org/10.1007/s12225-012-9412-2">10.1007/s12225-012-9412-2</a>.</p>
<p class="citation">Wyniger, Denise. 2011. Revision of the Nearctic genus <em>Coquillettia</em> Uhler with a transfer to the tribe Phylini, the description of 14 new species, a new synonymy, and the description of two new Nearctic genera <em>Leutiola</em> and <em>Ticua</em> and two new species (Heteroptera: Miridae: Phylinae). <em>Entomologica Americana</em> 117(3 &amp; 4): 134-211. doi:<a href="http://dx.doi.org/10.1664/11-RA-012.1">10.1664/11-RA-012.1</a></p>
<p class="citation">Zubov, Dmitry A. and Aaron P. Davis. 2012. <em>Galanthus panjutinii</em> sp. nov.: a new name for an invalidly published species of <em>Galanthus</em> (Amaryllidaceae) from the northern Colchis area of Western Transcaucasia. <em>Phytotaxa</em> 50: 55-63. (<a href="http://www.mapress.com/phytotaxa/content/2012/pt00050.htm">issue</a>)</p>
<div class="header"><h2><?php echo _("API Usage"); ?></h2></div>
<p><?php echo sprintf(_("The SimpleMappr API is used by The Missouri Botanical Garden's %s and The Encyclopedia of Life's %s as a custom %s module."), "<a href=\"http://www.tropicos.org/\">Tropicos</a>", "<a href=\"http://syrphidae.lifedesks.org/pages/24837\">LifeDesks</a>", "<a href=\"https://github.com/LifeDesks/LifeDesksExpert/tree/master/sites/all/modules/simplemappr\">Drupal</a>"); ?></p>
<div class="header"><h2><?php echo _("Code"); ?></h2></div>
<p><?php echo sprintf(_("The code behind SimpleMappr may be obtained at %s"), "<a href=\"https://github.com/dshorthouse/SimpleMappr\">https://github.com/dshorthouse/SimpleMappr</a>"); ?></p>
<div class="header"><h2><?php echo _("History"); ?></h2></div>
<p><?php echo _("The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History."); ?></p>
<div class="header"><h2><?php echo _("Acknowledgments"); ?></h2></div>
<p><?php echo sprintf(_("Underlying ArcView shapefiles were obtained from Natural Earth, %s and the mapping software used is MapServer, %s via PHP MapScript. Biodiversity Hotspot data were obtained from %s."), "<a href=\"http://www.naturalearthdata.com/\" target=\"_blank\">http://www.naturalearthdata.com/</a>", "<a href=\"http://mapserver.org\" target=\"_blank\">http://mapserver.org</a>", "<a href=\"http://www.conservation.org/where/priority_areas/hotspots/Pages/hotspots_main.aspx\" target=\"_blank\">Conservation International</a>"); ?></p>
</div>
<script type="text/javascript">
$(function() {
  $.getScript('http://widgets.twimg.com/j/2/widget.js', function() {
      twitter = new TWTR.Widget({
          version: 2,
             type: 'profile',
              rpp: 4,
         interval: 30000,
            width: 250,
           height: 300,
               id: 'twitter_div',
            theme: {
              shell: {
                background: '#e9e9e9',
                     color: '#222222'
              },
              tweets: {
                background: '#ffffff',
                     color: '#222222',
                     links: '#555555'
                }
              },
              features: {
                scrollbar: true,
                     loop: false,
                     live: true,
                 behavior: 'all'
              }
      }).render().setUser('SimpleMappr').start();
  });
});
</script>