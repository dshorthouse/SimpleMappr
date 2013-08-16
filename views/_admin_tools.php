<?php

function admin_tools() {

  $output = '';
  
  $output .= '<div class="header">';
  $output .= '<h2>' . _("Tools") . '</h2>';
  $output .= '</div>';
  $output .= '<ul class="fieldSets">';
  $output .= '<li><a href="#" id="flush-caches" class="admin-tool">' . _("Flush caches") . '</a></li>';
  $output .= '</ul>';
  
  echo $output;
}

?>