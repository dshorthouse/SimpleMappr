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

/**
 * Application class for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class MapprApplication extends Mappr
{
    /**
    * Set the headers and create the output
    *
    * @return void
    */
    public function create_output()
    {
        switch($this->output) {
        case 'tif':
            error_reporting(0);
            $this->image_url = $this->image->saveWebImage();
            $image_filename = basename($this->image_url);
            $clean_filename = self::clean_filename($this->file_name, $this->output);
            $filesize = filesize($this->tmp_path.$image_filename);
            Header::set_header('tif', $clean_filename, $filesize);
            ob_clean();
            flush();
            readfile($this->tmp_path.$image_filename);
            break;

        case 'png':
            error_reporting(0);
            $this->image_url = $this->image->saveWebImage();
            $image_filename = basename($this->image_url);
            $clean_filename = self::clean_filename($this->file_name, $this->output);
            $filesize = filesize($this->tmp_path.$image_filename);
            Header::set_header('png', $clean_filename, $filesize);
            ob_clean();
            flush();
            readfile($this->tmp_path.$image_filename);
            break;

        case 'svg':
            $clean_filename = self::clean_filename($this->file_name, $this->output);
            Header::set_header('svg', $clean_filename);
            $this->image->saveImage("");
            break;

        default:
            $this->image_url = $this->image->saveWebImage();

            $bbox = array(
                sprintf('%.10f', $this->map_obj->extent->minx + $this->ox_pad),
                sprintf('%.10f', $this->map_obj->extent->miny + $this->oy_pad),
                sprintf('%.10f', $this->map_obj->extent->maxx - $this->ox_pad),
                sprintf('%.10f', $this->map_obj->extent->maxy - $this->oy_pad)
            );

            $output = array(
                'mapOutputImage'      => $this->image_url,
                'size'                => $this->image_size,
                'rendered_bbox'       => implode(",", $bbox),
                'rendered_rotation'   => $this->rotation,
                'rendered_projection' => $this->projection,
                'legend_url'          => $this->legend_url,
                'scalebar_url'        => $this->scalebar_url,
                'bad_points'          => $this->get_bad_points()
            );

            return $output;
        }

    }

}