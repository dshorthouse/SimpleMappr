<?php

/**************************************************************************

File: mapprservice.pptx.class.php

Description: Produce a PPTX file from SimpleMappr. 

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
set_include_path(MAPPR_DIRECTORY . '/lib/PHPPowerPoint/');
include_once 'PHPPowerPoint.php';
include_once 'PHPPowerPoint/IOFactory.php';

class MAPPRPPTX extends MAPPR {

  private $_slidepadding = 25;

  /**
  * Get a user-defined file name, cleaned of illegal characters
  * @return string
  */
  public function get_file_name() {
    return preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $this->file_name);
  }

  public function get_output() {
      $objPHPPowerPoint = new PHPPowerPoint();

      // Set properties
      $objPHPPowerPoint->getProperties()->setCreator("SimpleMappr");
      $objPHPPowerPoint->getProperties()->setLastModifiedBy("SimpleMappr");
      $objPHPPowerPoint->getProperties()->setTitle($this->get_file_name());
      $objPHPPowerPoint->getProperties()->setSubject($this->get_file_name() . " point map");
      $objPHPPowerPoint->getProperties()->setDescription($this->get_file_name() . ", generated on SimpleMappr, http://www.simplemappr.net");
      $objPHPPowerPoint->getProperties()->setKeywords($this->get_file_name() . " SimpleMappr");

      // Create slide
      $currentSlide = $objPHPPowerPoint->getActiveSlide();
      $currentSlide->setSlideLayout(PHPPowerPoint_Slide_Layout::TITLE_AND_CONTENT);

      $width = 950;
      $height = 720;

      $files = array();
      $images = array('image', 'scale', 'legend');
      foreach($images as $image) {
        $files[$image]['file'] = MAPPR_DIRECTORY . $this->{$image}->saveWebImage();
        $files[$image]['size'] = getimagesize($files[$image]['file']);
      }

      $scale = ($files['image']['size'][0] > $width) ? $files['image']['size'][0]/$width : 1;

      foreach($files as $type => $value) {
        $size = getimagesize($value['file']);
        $shape = $currentSlide->createDrawingShape();
        $shape->setName('SimpleMappr ' . $this->get_file_name());
        $shape->setDescription('SimpleMappr ' . $this->get_file_name());
        $shape->setPath($value['file']);
        $shape->setWidth(round($value['size'][0]/$scale));
        $shape->setHeight(round($value['size'][1]/$scale));
        $shape_width = $shape->getWidth();
        $shape_height = $shape->getHeight();
        if($type == 'image') {
          $shape->setOffsetX(($width-$shape_width)/2);
          $shape->setOffsetY(($height-$shape_height)/2);
          $shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
        }
        if($type == 'scale') {
          $shape->setOffsetX($width-round($shape_width*1.5)-$this->_slidepadding);
          $shape->setOffsetY($height-round($shape_height*4)-$this->_slidepadding);
        }
        if($type == 'legend') {
          $shape->setOffsetX($width-$shape_width-$this->_slidepadding);
          $shape->setOffsetY(200);
        }
      }

      $shape = $currentSlide->createRichTextShape();
      $shape->setHeight(25);
      $shape->setWidth(450);
      $shape->setOffsetX($width - 450);
      $shape->setOffsetY($height - 10 - $this->_slidepadding);
      $shape->getAlignment()->setHorizontal(PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT);
      $shape->getAlignment()->setVertical(PHPPowerPoint_Style_Alignment::VERTICAL_CENTER);
      $textRun = $shape->createTextRun(_("Created with SimpleMappr, http://www.simplemappr.net"));
      $textRun->getFont()->setBold(true);
      $textRun->getFont()->setSize(12);

      // Output PowerPoint 2007 file
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private",false);
      header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
      header("Content-Disposition: attachment; filename=\"" . $this->get_file_name() . ".pptx\";" );
      header("Content-Transfer-Encoding: binary");
      $objWriter = PHPPowerPoint_IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
      $objWriter->save('php://output');
      exit();
  }

}
?>