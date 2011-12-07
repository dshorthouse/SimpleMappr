<?php

/**************************************************************************

File: mapprservice.docx.class.php

Description: Produce a DOCX file from SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once ('mapprservice.class.php');

/** PHPPowerPoint */
set_include_path(MAPPR_DIRECTORY . '/lib/PHPWord/');
include_once 'PHPWord.php';
include_once 'PHPWord/IOFactory.php';

class MAPPRDOCX extends MAPPR {

  private $_docpadding = 25;

  /**
  * Get a user-defined file name, cleaned of illegal characters
  * @return string
  */
  public function get_file_name() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name);
  }

  public function get_output() {
      $objPHPWord = new PHPWord();

      // Set properties
      $objPHPWord->getProperties()->setCreator("SimpleMappr");
      $objPHPWord->getProperties()->setLastModifiedBy("SimpleMappr");
      $objPHPWord->getProperties()->setTitle($this->get_file_name());
      $objPHPWord->getProperties()->setSubject($this->get_file_name() . " point map");
      $objPHPWord->getProperties()->setDescription($this->get_file_name() . ", generated on SimpleMappr, http://www.simplemappr.net");
      $objPHPWord->getProperties()->setKeywords($this->get_file_name() . " SimpleMappr");

      // Create section
      $section = $objPHPWord->createSection();

      $width = $section->getSettings()->getPageSizeW() - $section->getSettings()->getMarginLeft() - $section->getSettings()->getMarginRight();

      $files = array();
      $images = array('image', 'scale', 'legend');
      foreach($images as $image) {
        $files[$image]['file'] = MAPPR_DIRECTORY . $this->{$image}->saveWebImage();
        $files[$image]['size'] = getimagesize($files[$image]['file']);
      }

      // Width is measured as 'dxa', which is 1/20 of a point
      $scale = ($files['image']['size'][0]*20 > $width) ? $files['image']['size'][0]*20/$width : 1;

      foreach($files as $type => $values) {
        if($type == 'image') {
          $section->addImage($values['file'], array('width' => $values['size'][0]/$scale, 'height' => $values['size'][1]/$scale, 'align' => 'center'));
        } else {
          $section->addImage($values['file'], array('width' => $values['size'][0]/$scale, 'height' => $values['size'][1]/$scale, 'align' => 'right'));
        }
      }

      // Output Word 2007 file
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private",false);
      header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
      header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . ".docx\";" );
      header("Content-Transfer-Encoding: binary");
      $objWriter = PHPWord_IOFactory::createWriter($objPHPWord, 'Word2007');
      $objWriter->save('php://output');
      exit();
  }

}
?>