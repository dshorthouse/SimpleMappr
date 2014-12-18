<?php

function layers()
{
    $layers = array(
        'countries' => 'Countries',
        'stateprovinces' => 'State/Provinces',
        'lakesOutline' => 'lakes (outline)',
        'lakes' => 'lakes (greyscale)',
        'rivers' => 'rivers',
        'oceans' => 'oceans (greyscale)',
        'relief' => 'relief',
        'reliefgrey' => 'relief (greyscale)',
        'conservation' => 'biodiv. hotspots'
    );
    $output  = '';

    $output .= '<ul class="columns ui-helper-clearfix">';
    foreach($layers as $layer => $name) {
        $checked = '';
        if($layer === 'countries') {
            $checked = ' checked';
        }
        $output .= '<li>';
        $output .= '<input type="checkbox" id="'.$layer.'" class="layeropt" name="layers['.$layer.']"'.$checked.' />';
        $output .= '<label for="'.$layer.'">'._($name).'</label>';
        $output .= '</li>';
    }
    $output .= '</ul>';

    echo $output;
}