<?php

function point_layers()
{
    //marker sizes and shapes
    $marker_size  = '<option value="">'._("--select--").'</option>';
    $marker_size .= '<option value="6">6pt</option>';
    $marker_size .= '<option value="8">8pt</option>';
    $marker_size .= '<option value="10" selected="selected">10pt</option>';
    $marker_size .= '<option value="12">12pt</option>';
    $marker_size .= '<option value="14">14pt</option>';
    $marker_size .= '<option value="16">16pt</option>';

    $marker_shape  = '<option value="">'._("--select--").'</option>';
    $marker_shape .= '<option value="plus">'._("plus").'</option>';
    $marker_shape .= '<option value="cross">'._("cross").'</option>';
    $marker_shape .= '<option value="asterisk">'._("asterisk").'</option>';
    $marker_shape .= '<optgroup label="'._("solid").'">';
    $marker_shape .= '<option value="circle" selected="selected">'._("circle (s)").'</option>';
    $marker_shape .= '<option value="star">'._("star (s)").'</option>';
    $marker_shape .= '<option value="square">'._("square (s)").'</option>';
    $marker_shape .= '<option value="triangle">'._("triangle (s)").'</option>';
    $marker_shape .= '<option value="hexagon">'._("hexagon (s)").'</option>';
    $marker_shape .= '</optgroup>';
    $marker_shape .= '<optgroup label="'._("open").'">';
    $marker_shape .= '<option value="opencircle">'._("circle (o)").'</option>';
    $marker_shape .= '<option value="openstar">'._("star (o)").'</option>';
    $marker_shape .= '<option value="opensquare">'._("square (o)").'</option>';
    $marker_shape .= '<option value="opentriangle">'._("triangle (o)").'</option>';
    $marker_shape .= '<option value="openhexagon">'._("hexagon (o)").'</option>';
    $marker_shape .= '</optgroup>';

    $output = '';
    for ($i=0;$i<=NUMTEXTAREA-1;$i++) {
        $output .= '<div class="form-item fieldset-points">';
        $output .= '<button class="sprites-before removemore negative ui-corner-all" data-type="coords">'._("Remove").'</button>';
        $output .= '<h3><a href="#">'.sprintf(_("Layer %d"), $i+1).'</a></h3>' . "\n";
        $output .= '<div>' . "\n";
        $output .= '<div class="fieldset-taxon">' . "\n";
        $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="coords['.$i.'][title]" />' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<div class="resizable-textarea">' . "\n";
        $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="coords['.$i.'][data]"></textarea></span>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<div class="fieldset-extras">' . "\n";
        $output .= '<span class="fieldset-title">'._("Shape").':</span> <select class="m-mapShape" name="coords['.$i.'][shape]">'.$marker_shape.'</select> <span class="fieldset-title">'._("Size").':</span> <select class="m-mapSize" name="coords['.$i.'][size]">'.$marker_size.'</select>' . "\n";
        $output .= '<span class="fieldset-title">'._("Color").':</span> <input class="colorPicker" type="text" size="12" maxlength="11" name="coords['.$i.'][color]" value="0 0 0" />' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<button class="sprites-before clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</div>' . "\n";
    }

    echo $output;
}