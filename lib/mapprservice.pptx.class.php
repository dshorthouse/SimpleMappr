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
      $currentSlide->setSlideLayout( PHPPowerPoint_Slide_Layout::TITLE_AND_CONTENT );

      $images = array('image', 'scale', 'legend');
      foreach($images as $image) {
        $file = MAPPR_DIRECTORY . $this->{$image}->saveWebImage();
        $size = getimagesize($file);
        $shape = $currentSlide->createDrawingShape();
        $shape->setName('SimpleMappr ' . $this->get_file_name());
        $shape->setDescription('SimpleMappr ' . $this->get_file_name());
        $shape->setPath($file);
        $shape->setWidth($size[0]);
        $shape->setHeight($size[1]);
        if($image == 'image') {
          $shape->setOffsetX((950-round($size[0]))/2);
          $shape->setOffsetY((720-round($size[1]))/2);
        }
        if($image == 'scale') {
          $shape->setOffsetX(950-round($size[0]*1.5)-$this->_slidepadding);
          $shape->setOffsetY(720-round($size[1])*4-$this->_slidepadding);
        }
        if($image == 'legend') {
          $shape->setOffsetX(950-round($size[0])-$this->_slidepadding);
          $shape->setOffsetY(200);
        }
      }

      $shape = $currentSlide->createRichTextShape();
      $shape->setHeight(25);
      $shape->setWidth(450);
      $shape->setOffsetX(950 - 450 - $this->_slidepadding);
      $shape->setOffsetY(720 - 25 - $this->_slidepadding);
      $shape->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_RIGHT );
      $shape->getAlignment()->setVertical( PHPPowerPoint_Style_Alignment::VERTICAL_CENTER );
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