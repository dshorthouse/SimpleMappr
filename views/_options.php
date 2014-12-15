<?php

function options()
{
    $grids = array(1,5,10);

    $output  = '';
    $output .= '<ul>';
    $output .= '<li>';
    $output .= '<input type="checkbox" id="graticules" class="layeropt" name="layers[grid]"/><label for="graticules">graticules (grid)</label>';
    $output .= '<div id="graticules-selection">';
    $output .= '<input type="radio" id="gridspace" class="gridopt" name="gridspace" value="" checked="checked"/><label for="gridspace">fixed</label>';
    foreach($grids as $grid) {
        $output .= '<input type="radio" id="gridspace-'.$grid.'" class="gridopt" name="gridspace" value="'.$grid.'" />';
        $output .= '<label for="gridspace-'.$grid.'">'.$grid.'<sup>o</sup></label>';
    }
    $output .= '<input type="checkbox" id="gridlabel" name="gridlabel" /><label for="gridlabel">'._("hide labels").'</label>';
    $output .= '</div>';
    $output .= '</li>';
    $output .= '</ul>';

    echo $output;
}