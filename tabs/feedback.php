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
<div class="fb-like" data-href="<?php echo $_SERVER['HTTP_HOST']; ?>" data-send="false" data-layout="button_count" data-width="120" data-show-faces="false"></div>
</div>
<p class="ui-helper-clearfix">
<?php echo _("Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports."); ?>
</div>
<!-- Disqus BEGIN -->
<div id="disqus_thread"></div>
<div id="fb-root"></div>
<script type="text/javascript">
  var disqus_shortname = 'simplemappr',
  disqus_config = function() { this.language = "<?php echo $lang['canonical']; ?>"; };
  window.___gcfg = {lang: '<?php echo $lang['canonical']; ?>'};
(function(d, s) {
    var js, fjs = d.getElementsByTagName(s)[0], load = function(url, id) {
      if (d.getElementById(id)) { return; }
      js = d.createElement(s); js.src = url; js.id = id;
      fjs.parentNode.insertBefore(js, fjs);
    };
    load('//connect.facebook.net/<?php echo $lang['locale']; ?>/all.js#xfbml=1&appId=283657208313184', 'fbjssdk');
    load('https://apis.google.com/js/plusone.js', 'gplus1js');
    load('//platform.twitter.com/widgets.js', 'tweetjs');
    load('//' + disqus_shortname + '.disqus.com/embed.js', 'disqusjs');
}(document, 'script'));
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
<!-- Disqus END -->
</div>