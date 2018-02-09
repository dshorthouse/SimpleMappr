<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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
 */
namespace SimpleMappr\Mappr\Formats;

use \PhpOffice\PhpWord\Autoloader;
use \PhpOffice\PhpWord\PhpWord;
use \PhpOffice\PhpWord\IOFactory;
use SimpleMappr\Mappr\Mappr;
use SimpleMappr\Header;
use SimpleMappr\Request;
use SimpleMappr\Utility;

/**
 * DOCX handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Docx extends Mappr
{

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
        $objPHPWord = new PhpWord();

        $clean_filename = Utility::cleanFilename($this->request->file_name);

        // Set properties
        $properties = $objPHPWord->getDocInfo();
        $properties->setCreator('SimpleMappr');
        $properties->setTitle($clean_filename);
        $properties->setDescription($clean_filename . ", generated on SimpleMappr, " . MAPPR_URL);
        $properties->setLastModifiedBy("SimpleMappr");
        $properties->setSubject($clean_filename . " point map");
        $properties->setKeywords($clean_filename. ", SimpleMappr");

        // Add section
        $section = $objPHPWord->addSection();

        $width = $section->getStyle()->getPageSizeW() - $section->getStyle()->getMarginLeft() - $section->getStyle()->getMarginRight();

        $files = [];
        $images = ['image', 'scale', 'legend'];
        foreach ($images as $image) {
            if ($this->{$image}) {
                $image_filename = basename($this->{$image}->saveWebImage());
                $files[$image]['file'] = $this->tmp_path . $image_filename;
                $files[$image]['size'] = getimagesize($files[$image]['file']);
            }
        }

        // Width is measured as 'dxa', which is 1/20 of a point
        $scale = ($files['image']['size'][0]*20 > $width) ? $files['image']['size'][0]*20/$width : 1;

        foreach ($files as $type => $values) {
            if ($type == 'image') {
                $section->addImage($values['file'], ['width' => $values['size'][0]/$scale, 'height' => $values['size'][1]/$scale, 'align' => 'center']);
            } else {
                $section->addImage($values['file'], ['width' => $values['size'][0]/$scale, 'height' => $values['size'][1]/$scale, 'align' => 'right']);
            }
        }

        // Output Word 2007 file
        $objWriter = IOFactory::createWriter($objPHPWord, 'Word2007');
        Header::setHeader("docx", $clean_filename . ".docx");
        $objWriter->save('php://output');
    }
}
