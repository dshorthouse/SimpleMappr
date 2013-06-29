<?php

function hidden_inputs() {
  $inputs = array(
     1 => "download",
     2 => "output",
     3 => "download_token",
     4 => "bbox_map",
     5 => "projection_map",
     6 => "bbox_rubberband",
     7 => "bbox_query",
     8 => "pan",
     9 => "zoom_out",
    10 => "crop",
    11 => "rotation",
    12 => "save[title]",
    13 => "file_name",
    14 => "download_factor",
    15 => "width",
    16 => "height",
    17 => "download_filetype",
    18 => "grid_space",
    19 => "options[border]",
    20 => "options[legend]",
    21 => "options[scalebar]",
    22 => "options[scalelinethickness]",
    23 => "border_thickness",
    24 => "rendered_bbox",
    25 => "rendered_rotation",
    26 => "rendered_projection",
    27 => "legend_url",
    28 => "scalebar_url",
    29 => "bad_points"
  );

  $output = array();
  foreach($inputs as $key => $value) {
    $val = ($key >= 24) ? ' value=""' : "";
    $output[] = '<input type="hidden" name="'.$value.'" id="'.$value.'"'.$val.' />';
  }

  echo implode('', $output) . "\n";
}

?>