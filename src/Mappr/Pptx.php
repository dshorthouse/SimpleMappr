<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
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
namespace SimpleMappr\Mappr;

use \PhpOffice\PhpPresentation\Autoloader;
use \PhpOffice\PhpPresentation\PhpPresentation;
use \PhpOffice\PhpPresentation\IOFactory;
use \PhpOffice\PhpPresentation\Slide\Layout;
use \PhpOffice\PhpPresentation\Style\Color;
use \PhpOffice\PhpPresentation\Style\Alignment;

use SimpleMappr\Header;
use SimpleMappr\Request;
use SimpleMappr\Utility;

/**
 * PPTX handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Pptx extends Mappr
{
    /**
     * @var int $_slidepadding Padding around edges of slide
     */
    private $_slidepadding = 25;

    /**
    * Implement getRequest method
    *
    * @return obj
    */
    public function getRequest()
    {
        return Request::getRequest();
    }

    /**
     * Implement createOutput method
     *
     * @return void
     */
    public function createOutput()
    {
        $objPHPPowerPoint = new PhpPresentation();

        $clean_filename = Utility::cleanFilename($this->request->file_name);

        // Set properties
        $properties = $objPHPPowerPoint->getDocumentProperties();
        $properties->setCreator("SimpleMappr");
        $properties->setLastModifiedBy("SimpleMappr");
        $properties->setTitle($clean_filename);
        $properties->setSubject($clean_filename . " point map");
        $properties->setDescription($clean_filename . ", generated on SimpleMappr, " . MAPPR_URL);
        $properties->setKeywords($clean_filename . " SimpleMappr");

        // Create slide
        $currentSlide = $objPHPPowerPoint->getActiveSlide();
        $currentSlide->setSlideLayout(Layout::TITLE_AND_CONTENT);

        $width = 950;
        $height = 720;

        $files = [];
        $images = ['image', 'scale', 'legend'];
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
        $textRun = $shape->createTextRun(_("Created with SimpleMappr, " . MAPPR_URL));
        $textRun->getFont()->setBold(true);
        $textRun->getFont()->setSize(12);

        // Output PowerPoint 2007 file
        $objWriter = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
        Header::setHeader("pptx", $clean_filename . ".pptx");
        $objWriter->save('php://output');
    }

}