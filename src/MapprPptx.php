<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link      http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @package   SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */
namespace SimpleMappr;

use \PhpOffice\PhpPowerpoint\Autoloader;
use \PhpOffice\PhpPowerpoint\PhpPowerpoint;
use \PhpOffice\PhpPowerpoint\Slide\Layout;
use \PhpOffice\PhpPowerpoint\Style\Alignment;
use \PhpOffice\PhpPowerpoint\IOFactory;

/**
 * PPTX handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class MapprPptx extends Mappr
{
    private $_slidepadding = 25;

    public function create_output()
    {
        require_once ROOT . "/vendor/phpoffice/phppowerpoint/src/PhpPowerpoint/Autoloader.php";

        Autoloader::register();
        $objPHPPowerPoint = new PhpPowerpoint();

        $clean_filename = parent::clean_filename($this->file_name);

        // Set properties
        $properties = $objPHPPowerPoint->getProperties();
        $properties->setCreator("SimpleMappr");
        $properties->setLastModifiedBy("SimpleMappr");
        $properties->setTitle($clean_filename);
        $properties->setSubject($clean_filename . " point map");
        $properties->setDescription($clean_filename . ", generated on SimpleMappr, http://www.simplemappr.net");
        $properties->setKeywords($clean_filename . " SimpleMappr");

        // Create slide
        $currentSlide = $objPHPPowerPoint->getActiveSlide();
        $currentSlide->setSlideLayout(Layout::TITLE_AND_CONTENT);

        $width = 950;
        $height = 720;

        $files = array();
        $images = array('image', 'scale', 'legend');
        foreach ($images as $image) {
            if ($this->{$image}) {
                $image_filename = basename($this->{$image}->saveWebImage());
                $files[$image]['file'] = $this->tmp_path . $image_filename;
                $files[$image]['size'] = getimagesize($files[$image]['file']);
            }
        }

        $scale = 1;
        $scaled_w = $files['image']['size'][0];
        $scaled_h = $files['image']['size'][1];
        if ($scaled_w > $width || $scaled_h > $height) {
            $scale = ($scaled_w/$width > $scaled_h/$height) ? $scaled_w/$width : $scaled_h/$height;
        }

        foreach ($files as $type => $value) {
            $size = getimagesize($value['file']);
            $shape = $currentSlide->createDrawingShape();
            $shape->setName('SimpleMappr ' . $clean_filename);
            $shape->setDescription('SimpleMappr ' . $clean_filename);
            $shape->setPath($value['file']);
            $shape->setWidth(round($value['size'][0]/$scale));
            $shape->setHeight(round($value['size'][1]/$scale));
            $shape_width = $shape->getWidth();
            $shape_height = $shape->getHeight();
            if ($type == 'image') {
                $shape->setOffsetX(($width-$shape_width)/2);
                $shape->setOffsetY(($height-$shape_height)/2);
            }
            if ($type == 'scale') {
                $shape->setOffsetX($width-round($shape_width*1.5)-$this->_slidepadding);
                $shape->setOffsetY($height-round($shape_height*4)-$this->_slidepadding);
            }
            if ($type == 'legend') {
                $shape->setOffsetX($width-$shape_width-$this->_slidepadding);
                $shape->setOffsetY(200);
            }
        }

        $shape = $currentSlide->createRichTextShape();
        $shape->setHeight(25);
        $shape->setWidth(450);
        $shape->setOffsetX($width - 450);
        $shape->setOffsetY($height - 10 - $this->_slidepadding);
        $alignment = $shape->getActiveParagraph()->getAlignment();
        $alignment->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $alignment->setVertical(Alignment::VERTICAL_CENTER);
        $textRun = $shape->createTextRun(_("Created with SimpleMappr, http://www.simplemappr.net"));
        $textRun->getFont()->setBold(true);
        $textRun->getFont()->setSize(12);

        // Output PowerPoint 2007 file
        $objWriter = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
        Header::set_header("pptx", $clean_filename . ".pptx");
        $objWriter->save('php://output');
    }

}