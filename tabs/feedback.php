<?php
require_once('../lib/mapprservice.usersession.class.php');
$lang = USERSESSION::select_language();
$tweet = ($lang['canonical'] == 'en') ? 'Tweet' : 'Tweeter';
?>
<style type="text/css">
.fb_iframe_widget,.twitter-share-button{vertical-align:top;}
.dsq-avatar{top:0 !important;right:7em !important;}
</style>
<div id="map-feedback">
<div id="general-feedback" class="panel ui-corner-all">
<p>
<?php echo _("Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports."); ?>
<p><fb:like href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/" width="50" height="80" data-layout="button_count" data-show-faces="false" />
<a href="https://twitter.com/share" class="twitter-share-button" data-lang="<?php echo $lang['canonical']; ?>"><?php echo $tweet; ?></a>
</p>
</div>
<!-- Disqus BEGIN -->
<div id="disqus_thread"></div>
<script type="text/javascript">
  var disqus_shortname = 'simplemappr',
  disqus_config = function() { this.language = "<?php echo $lang['canonical']; ?>"; };
  function remove(id) {
    return (elem=document.getElementById(id)).parentNode.removeChild(elem);
  }
  (function(d, s, id) {
   var js, djs=d.getElementsByTagName(s)[0];
   if(d.getElementById(id)) { remove(id); }
   js=d.createElement(s); js.id=id;
   js.src='//' + disqus_shortname + '.disqus.com/embed.js';
   djs.parentNode.insertBefore(js, djs);
  })(document, 'script', 'disqus-wjs');
  (function(d, s, id) {
    var js, fjs=d.getElementsByTagName(s)[0];
    if(d.getElementById(id)) { remove(id); }
    js=d.createElement(s); js.id=id;
    js.src="//connect.facebook.net/<?php echo $lang['locale']; ?>/all.js#xfbml=1";
    fjs.parentNode.insertBefore(js, fjs);
  })(document, 'script', 'facebook-jssdk');
  (function(d, s, id) {
    var js, fjs=d.getElementsByTagName(s)[0];
    if(d.getElementById(id)) { remove(id); }
    js=d.createElement(s); js.id=id;
    js.src="//platform.twitter.com/widgets.js";
    fjs.parentNode.insertBefore(js,fjs);
  })(document, 'script', 'twitter-wjs');
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
<!-- Disqus END -->
</div>