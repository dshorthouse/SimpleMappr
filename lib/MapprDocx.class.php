<?php

/********************************************************************

MapprDocx.class.php released under MIT License
Extends Mappr class to produce DOCX files for download on SimpleMappr
Depends on PHPWord, http://phpword.codeplex.com/

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

namespace SimpleMappr;

class MapprDocx extends Mappr {

  public function create_output() {

    /** PHPWord */
    require_once(ROOT . '/vendor/phpoffice/phpword/src/PhpWord/Autoloader.php');
    \PhpOffice\PhpWord\Autoloader::register();

    $objPHPWord = new \PhpOffice\PhpWord\PhpWord();

    $clean_filename = parent::clean_filename($this->file_name);

    // Set properties
    $properties = $objPHPWord->getDocumentProperties();
    $properties->setCreator('SimpleMappr');
    $properties->setTitle($clean_filename);
    $properties->setDescription($clean_filename . ", generated on SimpleMappr, http://www.simplemappr.net");
    $properties->setLastModifiedBy("SimpleMappr");
    $properties->setSubject($clean_filename . " point map");
    $properties->setKeywords($clean_filename. ", SimpleMappr");

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
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($objPHPWord, 'Word2007');
    Utilities::set_header("docx");
    header("Content-Disposition: attachment; filename=\"" . $clean_filename . ".docx\";" );
    $objWriter->save('php://output');
  }

}