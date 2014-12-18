<?php

function projections($args)
{
    $output  = '';
    $output .= '<ul>';
    $output .= '<li>';
    $output .= '<select id="projection" name="projection">';
    foreach ($args[0] as $key => $value) {
      $selected = ($value['name'] == 'Geographic') ? ' selected="selected"': '';
      $output .= '<option value="'.$key.'"'.$selected.'>'.$value['name'].'</option>';
    }
    $output .= '</select>';
    $output .= '</li>';
    $output .= '<li id="origin-selector">';
    $output .= '<label for="origin">'._("longitude of natural origin").'</label><input type="text" id="origin" name="origin" size="4" />';
    $output .= '</li>';
    $output .= '</ul>';

    echo $output;
}