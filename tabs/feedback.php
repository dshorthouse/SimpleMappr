<?php session_start(); ?>
    <!-- feedback tab -->
    <div id="map-feedback">
        <div id="general-feedback" class="panel ui-corner-all">
            <p><?php echo _('Used SimpleMappr in a manuscript, poster, PowerPoint presentation or are you making use of the API? Please also drop a note if you have feature requests or bug reports.'); ?></p>
        </div>
<?php if(isset($_SESSION['simplemappr'])): ?>
    <div id="map-chat">
        <iframe src="http://www.google.com/talk/service/badge/Show?tk=z01q6amlq1fjbjcl5e3knmamt36ttjqekn0h4t4g14aoaoje75ac2muv8p83ol0tb6r7174nlco10fp8sv5jifs6fd7vq5n8ap2cfaib43ajhqu3g1c6iet7teo4cjjhnk6qq6q0tdt8olcsa725udlkr0l4tvo7jd1pllav14pk0g2vn86st72jpd8iep1u4sc&amp;w=200&amp;h=60" allowtransparency="true" width="200" frameborder="0" height="60"></iframe>
       <div><?php echo _('Chat with the SimpleMappr developer'); ?></div>
     </div>
<?php endif; ?>
     <!-- Disqus BEGIN -->
     <div id="disqus_thread"></div>
     <script type="text/javascript">
  (function() {
   var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
   dsq.src = 'http://simplemappr.disqus.com/embed.js';
   (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
  })();
</script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=simplemappr">comments</a>.</noscript>
    <!-- Disqus END -->

    <!-- close feedback tab -->
    </div>