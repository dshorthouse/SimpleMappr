<?php

function labels()
{
    $labels = array(
        'countrynames' => 'Countries',
        'stateprovnames' => 'State/Provinces',
        'lakenames' => 'lakes',
        'rivernames' => 'rivers',
        'placenames' => 'places',
        'physicalLabels' => 'physical',
        'marineLabels' => 'marine',
        'hotspotLabels' => 'biodiv. hotspots'
    );

    $output  = '';
    $output .= '<ul class="columns ui-helper-clearfix">';
    foreach($labels as $label => $name) {
        $output .= '<li>';
        $output .= '<input type="checkbox" id="'.$label.'" class="layeropt" name="layers['.$label.']" />';
        $output .= '<label for="'.$label.'">'._($name).'</label>';
        $output .= '</li>';
    }
    $output .= '</ul>';

    echo $output;
}