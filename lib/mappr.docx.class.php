<?php

/**************************************************************************

File: mappr.docx.class.php

Description: Produce a DOCX file from SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

require_once ('mappr.class.php');
set_include_path(dirname(__FILE__) . '/PHPWord/');
include_once 'PHPWord.php';
include_once 'PHPWord/IOFactory.php';

class MapprDocx extends Mappr {

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
        if($this->{$image}) {
          $image_filename = basename($this->{$image}->saveWebImage());
          $files[$image]['file'] = $this->tmp_path . $image_filename;
          $files[$image]['size'] = getimagesize($files[$image]['file']);
        }
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