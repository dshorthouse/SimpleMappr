<?php
require_once('../lib/mapprservice.usersession.class.php');
$lang = USERSESSION::select_language();
?>
<div id="map-feedback">
<div id="general-feedback" class="panel ui-corner-all">
<p><?php echo _("Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports."); ?></p>
</div>
<!-- Disqus BEGIN -->
<div id="disqus_thread"></div>
<script type="text/javascript">
  var disqus_shortname = 'simplemappr',
  disqus_config = function() { this.language = "<?php echo $lang['canonical']; ?>"; };
  (function() {
   var dsq = document.createElement('script');
   dsq.type = 'text/javascript'; dsq.async = true;
   dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
   (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
  })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
<!-- Disqus END -->
</div>