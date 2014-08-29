<?php

function hidden_inputs()
{
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
        10 => "zoom_in",
        11 => "crop",
        12 => "rotation",
        13 => "save[title]",
        14 => "file_name",
        15 => "download_factor",
        16 => "width",
        17 => "height",
        18 => "download_filetype",
        19 => "grid_space",
        20 => "options[border]",
        21 => "options[legend]",
        22 => "options[scalebar]",
        23 => "options[scalelinethickness]",
        24 => "border_thickness",
        25 => "rendered_bbox",
        26 => "rendered_rotation",
        27 => "rendered_projection",
        28 => "legend_url",
        29 => "scalebar_url",
        30 => "bad_points"
    );

    $output = array();
    foreach ($inputs as $key => $value) {
        $val = ($key >= 24) ? ' value=""' : "";
        $output[] = '<input type="hidden" name="'.$value.'" id="'.$value.'"'.$val.' />';
    }

    echo implode('', $output) . "\n";
}