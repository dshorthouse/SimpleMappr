<?php

function regions()
{
    $output = '';
    for ($i=0;$i<=NUMTEXTAREA-1;$i++) {
        $output .= '<div class="form-item fieldset-regions">';
        $output .= '<button class="sprites-before removemore negative ui-corner-all" data-type="regions">'._("Remove").'</button>';
        $output .= '<h3><a href="#">'.sprintf(_("Region %d"), $i+1).'</a></h3>' . "\n";
        $output .= '<div>' . "\n";
        $output .= '<div class="fieldset-taxon">' . "\n";
        $output .= '<span class="fieldset-title">'._("Legend").'<span class="required">*</span>:</span> <input type="text" class="m-mapTitle" size="40" maxlength="40" name="regions['.$i.'][title]" />' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<div class="resizable-textarea">' . "\n";
        $output .= '<span><textarea class="resizable m-mapCoord" rows="5" cols="60" name="regions['.$i.'][data]"></textarea></span>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<div class="fieldset-extras">' . "\n";
        $output .= '<span class="fieldset-title">'._("Color").':</span> <input type="text" class="colorPicker" size="12" maxlength="11" name="regions['.$i.'][color]" value="150 150 150" />' . "\n";
        $output .= '</div>' . "\n";
        $output .= '<button class="sprites-before clear clearself negative ui-corner-all">'._("Clear").'</button>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</div>' . "\n";
    }

    echo $output;	
}