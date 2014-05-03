<?php

function admin_citations() {
  $output = "";
  $output .= '<div class="header">';
  $output .= '<h2>' . _("Citations") . '</h2>';
  $output .= '</div>';

  $output .= '<p>';
  $output .= '<label for="citation-reference">' . _("Formatted reference") . '<span class="required">*</span></label>';
  $output .= '<textarea id="citation-reference" class="resizable citation" rows="5" cols="60" name="citation[reference]"></textarea>';
  $output .= '</p>';

  $output .= '<p>';
  $output .= '<label for="citation-surname">' . _("Author surname") . '<span class="required">*</span></label>';
  $output .= '<input type="text" id="citation-surname"  class="citation" name="citation[first_author_surname]" size="60" />';
  $output .= '</p>';

  $output .= '<p>';
  $output .= '<label for="citation-year">' . _("Year") . '<span class="required">*</span></label>';
  $output .= '<input type="text" id="citation-year" class="citation" name="citation[year]" size="10" />';
  $output .= '</p>';

  $output .= '<p>';
  $output .= '<label for="citation-doi">' . _("DOI") . '</label>';
  $output .= '<input type="text" id="citation-doi" class="citation" name="citation[doi]" size="60" />';
  $output .= '</p>';

  $output .= '<p>';
  $output .= '<label for="citation-link">' . _("Link") . '</label>';
  $output .= '<input type="text" id="citation-link" class="citation" name="citation[link]" size="60" />';
  $output .= '</p>';
  
  $output .= '<p>';
  $output .= '<button class="sprites-before addmore positive ui-corner-all">' . _("Add citation") . '</button>';
  
  echo $output;
}