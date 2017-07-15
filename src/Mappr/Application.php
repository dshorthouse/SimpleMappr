<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
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
namespace SimpleMappr\Mappr;

use SimpleMappr\Request;
use SimpleMappr\Utility;
use SimpleMappr\Header;

/**
 * Application class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Application extends Mappr
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
        switch ($this->request->output) {
        case 'tif':
            error_reporting(0);
            $this->image_url = $this->image->saveWebImage();
            $image_filename = basename($this->image_url);
            $clean_filename = Utility::cleanFilename($this->request->file_name, $this->request->output);
            $filesize = filesize($this->tmp_path.$image_filename);
            Header::setHeader('tif', $clean_filename, $filesize);
            ob_clean();
            flush();
            readfile($this->tmp_path.$image_filename);
            break;

        case 'png':
            if ($this->request->download) {
                error_reporting(0);
                $this->image_url = $this->image->saveWebImage();
                $image_filename = basename($this->image_url);
                $clean_filename = Utility::cleanFilename($this->request->file_name, $this->request->output);
                $filesize = filesize($this->tmp_path.$image_filename);
                Header::setHeader('png', $clean_filename, $filesize);
                ob_clean();
                flush();
                readfile($this->tmp_path.$image_filename);
            } else {
                Header::setHeader('json');
                return json_encode($this->_defaultOutput());
            }
            break;

        case 'svg':
            $clean_filename = Utility::cleanFilename($this->request->file_name, $this->request->output);
            Header::setHeader('svg', $clean_filename);
            $this->image->saveImage("");
            break;

        default:
            Header::setHeader('json');
            return json_encode($this->_defaultOutput());
        }
    }

    /**
     * Produce an array for the default output
     *
     * @return array
     */
    private function _defaultOutput()
    {
        $this->image_url = $this->image->saveWebImage();

        $bbox = [
          sprintf('%.10f', $this->map_obj->extent->minx + $this->ox_pad),
          sprintf('%.10f', $this->map_obj->extent->miny + $this->oy_pad),
          sprintf('%.10f', $this->map_obj->extent->maxx - $this->ox_pad),
          sprintf('%.10f', $this->map_obj->extent->maxy - $this->oy_pad)
        ];

        $output = [
          'mapOutputImage'      => $this->image_url,
          'size'                => $this->image_size,
          'rendered_bbox'       => implode(",", $bbox),
          'rendered_rotation'   => $this->request->rotation,
          'rendered_projection' => $this->request->projection,
          'legend_url'          => $this->legend_url,
          'scalebar_url'        => $this->scalebar_url,
          'bad_points'          => $this->getBadPoints(),
          'bad_drawings'        => $this->getBadDrawings()
        ];

        return $output;
    }
}
