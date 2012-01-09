<?php
require_once('../lib/mapprservice.usersession.class.php');
$lang = USERSESSION::select_language();
$tweet = ($lang['canonical'] == 'en') ? 'Tweet' : 'Tweeter';
?>
<style type="text/css">
#social{border-left:1px solid #ccc;width:15%;float:right;padding-left:10px;}
#general-feedback .ui-helper-clearfix{margin-left:0;}
</style>
<div id="map-feedback">
<div id="general-feedback" class="panel ui-corner-all">
<div id="social">
<g:plusone size="medium" annotation="inline" width="120"></g:plusone>
<a href="https://twitter.com/share" class="twitter-share-button" data-text="@SimpleMappr" data-url="http://<?php echo $_SERVER['HTTP_HOST']; ?>" data-lang="<?php echo $lang['canonical']; ?>"><?php echo $tweet; ?></a>
<fb:like href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/" width="120" data-layout="button_count" data-show-faces="false"></fb:like>
</div>
<p class="ui-helper-clearfix">
<?php echo _("Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports."); ?>
</div>
<!-- Disqus BEGIN -->
<div id="disqus_thread"></div>
<script type="text/javascript">
  var disqus_shortname = 'simplemappr',
  disqus_config = function() { this.language = "<?php echo $lang['canonical']; ?>"; };
  window.___gcfg = {lang: '<?php echo $lang['canonical']; ?>'};
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
  (function(d, s, id) {
    var js, pls=d.getElementsByTagName('script')[0];
    if(d.getElementById(id)) { remove(id); }
    js=d.createElement(s); js.id=id;
    js.src='https://apis.google.com/js/plusone.js';
    pls.parentNode.insertBefore(js, pls);
  })(document, 'script', 'google-plus-1');
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
<!-- Disqus END -->
</div>