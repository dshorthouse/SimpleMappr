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
</style>
<div id="map-about">
<div id="general-about" class="panel ui-corner-all">
<p><?php echo _("Create greyscale point maps suitable for reproduction on print media by copying and pasting geographic coordinates in layers, choosing pushpin styles, then downloading the result."); ?></p>
</div>
<div class="header"><h2><?php echo _("Citing"); ?></h2></div>
<p>Shorthouse, David P. 2010. SimpleMappr, an online tool to produce publication-quality point maps. Retrieved from http://www.simplemappr.net. Accessed <?php echo date("Y-m-d"); ?>.</p>
<div class="ui-helper-clearfix">
<div id="recent-updates">
<div class="header"><h2><?php echo _("Recent Updates"); ?></h2></div>
<dl>
<dt class="update"><?php echo _("July 2, 2012"); ?></dt><dd><?php echo _("Improvements: Added svg as an embedded output format."); ?></dd>
<dt class="update"><?php echo _("July 1, 2012"); ?></dt><dd><?php echo _("Improvements: Added sort to columns in My Maps."); ?></dd>
<dt class="update"><?php echo _("June 14, 2012"); ?></dt><dd><?php echo _("Bug fixes: Download when legend selected in absence of regions or coordinates resulted in error thrown."); ?></dd>
<dt class="update"><?php echo _("April 2, 2012"); ?></dt><dd><?php echo _("Bug fixes: Map coordinates in DDMMSS copied/pasted from Excel now correctly interpreted."); ?></dd>
<dt class="update"><?php echo _("February 8, 2012"); ?></dt><dd><?php echo _("Bug fixes: Map coordinates in DDMMSS now properly download."); ?></dd>
<dt class="update"><?php echo _("January 2, 2012"); ?></dt><dd><?php echo _("Improvements: Enhanced the recognition of coordinates to include both decimal degrees and DDMMSS."); ?></dd>
<dt class="update"><?php echo _("January 1, 2012"); ?></dt><dd><?php echo _("Improvements: Added undo and redo. Bug fixes: The saved map list now shows for Internet Explorer users."); ?></dd>
<dt class="update"><?php echo _("December 21, 2011"); ?></dt><dd><?php echo _("Improvements: Settings panel may now be collapsed/expanded."); ?></dd>
<dt class="update"><?php echo _("December 9, 2011"); ?></dt><dd><?php echo _("Bug fixes: Zoom now executes when rectangle is drawn at edge of map (reported by GIS Unit Kew)."); ?></dd>
<dt class="update"><?php echo _("December 8, 2011"); ?></dt><dd><?php echo _("Improvements: Added a border thickness slider."); ?></dd>
<dt class="update"><?php echo _("November 23, 2011"); ?></dt><dd><?php echo _("Improvements: Added download as Word (.docx)."); ?></dd>
<dt class="update"><?php echo _("November 22, 2011"); ?></dt><dd><?php echo _("Improvements: Added download as PowerPoint (.pptx)."); ?></dd>
<dt class="update"><?php echo _("November 18, 2011"); ?></dt><dd><?php echo _("Improvements: Added crop and download dimension indicators. The crop dimensions may be directly adjusted just as can the coordinates in the floating crop window. Bug fixes: specifying width and height of embedded images did not work as expected."); ?></dd>
<dt class="update"><?php echo _("November 17, 2011"); ?></dt><dd><?php echo _("Improvements: Cleaner URL for image embed; added KML and GeoJSON as embed options."); ?></dd>
</dl>
</div>
<div id="live-updates">
<div class="header"><h2><?php echo _("Live Updates"); ?></h2></div>
<div id="twitter_div"></div>
</div>
</div>
<div class="header"><h2><?php echo _("In the Wild"); ?></h2></div>
<p class="citation">Alves, V.R., R.A. de Freitas, F.L. Santos, A.F.J. de Oliveira, T.V. Barrett, and P.H.F. Shimabukuro. 2012. Sand flies (Diptera, Psychodidae, Phlebotominae) from Central Amazonia and four new records for the Amazonas state, Brazil. Revista Brasileira de Entomologia, (ahead), 0-0. doi:<a href="http://dx.doi.org/10.1590/S0085-56262012005000020">10.1590/S0085-56262012005000020</a>.</p>
<p class="citation">Borrero, Francisco J. and Abraham S.H. Breure. 2011. The Amphibulimidae (Mollusca: Gastropoda: Orthalicoidea) from Colombia and adjacent areas. <em>Zootaxa</em> 3054: 1-59. <a href="http://www.mapress.com/zootaxa/list/2011/3054.html">permalink</a>.
<p class="citation">Breure, Abraham and Corey Whisson. 2012. Annotated type catalogue of <em>Bothriembryon</em> (Mollusca, Gastropoda, Orthalicoidea) in Australian museums, with a compilation of types in other museums. <em>ZooKeys</em> 194: 41-80. doi:<a href="http://dx.doi.org/10.3897/zookeys.194.2721">10.3897/zookeys.194.2721</a></p>
<p class="citation">Brothers, Denis J. 2012. The new genus <em>Ancistrotilla</em> n. gen., with new species from Vanuatu and New Caledonia (Hymenoptera, Mutillidae). <em>Zoosystema</em> 34(2): 223-251. doi:<a href="http://dx.doi.org/10.5252/z2012n2a2">10.5252/z2012n2a2</a></p>
<p class="citation">Carr, Christina May. 2011. Polychaete diversity and distribution patterns in Canadian marine waters. <em>Marine Biodiversity</em> Online first, doi:<a href="http://dx.doi.org/10.1007/s12526-011-0095-y">10.1007/s12526-011-0095-y</a></p>
<p class="citation">Carr, C.M., Hardy, S.M., Brown, T.M., Macdonald, T.A., Hebert, P.D.N. 2011. A Tri-Oceanic Perspective: DNA Barcoding Reveals Geographic Structure and Cryptic Diversity in Canadian Polychaetes. <em>PLoS ONE</em> 6(7): e22232. doi:<a href="http://dx.doi.org/10.1371/journal.pone.0022232">10.1371/journal.pone.0022232</a></p>
<p class="citation">Cuzepan, Gabriela. 2011. Diving beetles (Coleoptera: Dytiscidae) from the Transylvanian Society collection of The Natural History Museum of Sibiu (Romania). <em>Travaux du Muséum National d’Histoire Naturelle</em> 54(1): 69-87. doi:<a href="http://dx.doi.org/10.2478/v10191-011-0005-3">10.2478/v10191-011-0005-3</a></p>
<p class="citation">Inclan Luna, Diego Javier. 2010. Revision of the genus <em>Erythromelana</em> Townsend, 1919 (Diptera: Tachinidae) with notes on their phylogeny and diversification. Master of Science (MS), Wright State University, Biological Sciences (<a href="http://rave.ohiolink.edu/etdc/view?acc_num=wright1292306222">permalink</a>)</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. <em>Hoya mappigera</em> (Apocynaceae, Asclepiadoideae), a new campanulate flowered species from Peninsular Malaysia and southern Thailand. <em>Feddes Repertorium</em>. doi:<a href="http://dx.doi.org/10.1002/fedr.201100019">10.1002/fedr.201100019</a>.</p>
<p class="citation">Rodda, M. and N.S. Juhonewe. 2012. Taxonomic notes on the long-lost <em>Hoya burmanica</em> (Apocynaceae, Asclepiadoideae) from Myanmar. Kew Bulletin. 1-6. doi:<a href="http://dx.doi.org/10.1007/s12225-012-9377-1">10.1007/s12225-012-9377-1</a>.</p>
<p class="citation">Scudder, G.G.E. and Michael D. Schwartz. 2012. Two new species of <em>Trigonotylus</em> (Hemiptera: Heteroptera: Miridae: Stenodemini) from western Canada and northwestern United States. <em>Zootaxa</em> 3174: 51-58. (<a href="http://www.mapress.com/zootaxa/2012/f/zt03174p058.pdf">PDF</a>, 1.4MB)</p>
<p class="citation">Skevington, J.H. and F.C. Thompson. 2012. Review of New World <em>Sericomyia</em> (Diptera, Syrphidae), including description of a new species. <em>The Canadian Entomologist</em>. 144: 216-247. doi:<a href="http://dx.doi.org/10.4039/tce.2012.24">10.4039/tce.2012.24</a>.</p>
<p class="citation">Wyniger, Denise. 2011. Revision of the Nearctic genus <em>Coquillettia</em> Uhler with a transfer to the tribe Phylini, the description of 14 new species, a new synonymy, and the description of two new Nearctic genera <em>Leutiola</em> and <em>Ticua</em> and two new species (Heteroptera: Miridae: Phylinae). <em>Entomologica Americana</em> 117(3 &amp; 4): 134-211. doi:<a href="http://dx.doi.org/10.1664/11-RA-012.1">10.1664/11-RA-012.1</a></p>
<p class="citation">Zubov, Dmitry A. and Aaron P. Davis. 2012. <em>Galanthus panjutinii</em> sp. nov.: a new name for an invalidly published species of <em>Galanthus</em> (Amaryllidaceae) from the northern Colchis area of Western Transcaucasia. <em>Phytotaxa</em> 50: 55-63. (<a href="http://www.mapress.com/phytotaxa/content/2012/pt00050.htm">issue</a>)</p>
<div class="header"><h2><?php echo _("Code"); ?></h2></div>
<p><?php echo sprintf(_("The code behind SimpleMappr may be obtained at %s"), "<a href=\"https://github.com/dshorthouse/SimpleMappr\">https://github.com/dshorthouse/SimpleMappr</a>"); ?></p>
<div class="header"><h2><?php echo _("History"); ?></h2></div>
<p><?php echo _("The first version of this application was developed by David P. Shorthouse to help participants in two Planetary Biodiversity Inventory (National Science Foundation) projects create publication-quality maps. Funding for that work was coordinated by Dr. Norman Platnick, American Museum of Natural History."); ?></p>
<div class="header"><h2><?php echo _("Acknowledgments"); ?></h2></div>
<p><?php echo sprintf(_("Underlying ArcView shapefiles were obtained from Natural Earth, %s and the mapping software used is MapServer, %s via PHP MapScript."), "<a href=\"http://www.naturalearthdata.com/\" target=\"_blank\">http://www.naturalearthdata.com/</a>", "<a href=\"http://mapserver.org\" target=\"_blank\">http://mapserver.org</a>"); ?></p>
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