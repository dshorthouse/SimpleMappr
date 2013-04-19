<?php

function filetypes() {
  $output = '';
  $file_types = array('svg', 'png', 'tif', 'pptx', 'docx', 'kml');
  foreach($file_types as $type) {
    $extra = '';
    $checked = ($type == "svg") ? ' checked="checked"': '';
    $output .= '<input type="radio" id="download-'.$type.'" class="download-filetype" name="download-filetype" value="'.$type.'"'.$checked.' />';
    $asterisk = ($type == "svg" || $type == "kml") ? '*' : '';
    if($type == 'kml') { $extra = ' (Google Earth)'; }
    if($type == 'pptx') { $extra = ' (PowerPoint)'; }
    if($type == 'docx') { $extra = ' (Word)'; }
    $output .= '<label for="download-'.$type.'">'.$type.$asterisk.$extra.'</label>';
  }

  echo $output;
}

?>