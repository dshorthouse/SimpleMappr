<?php

function scales()
{
    $output = '';
    $file_sizes = array(1,3,4,5);
    foreach ($file_sizes as $size) {
        $checked = ($size == 1) ? ' checked="checked"' : '';
        $output .= '<input type="radio" id="download-factor-'.$size.'" class="download-factor" name="download-factor" value="'.$size.'"'.$checked.' />';
        $output .= '<label for="download-factor-'.$size.'">'.$size.'X</label>';
    }

    echo $output;
}