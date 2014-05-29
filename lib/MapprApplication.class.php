<?php
namespace SimpleMappr;

/**
 * MapprApplication.class.php released under MIT License
 * Mappr Application class for SimpleMappr
 *
 * PHP Version >= 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse {{{
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
 * }}}
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
            Header::set_header('tif');
            header("Content-Disposition: attachment; filename=\"" . self::clean_filename($this->file_name, $this->output) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($this->tmp_path.$image_filename));
            ob_clean();
            flush();
            readfile($this->tmp_path.$image_filename);
            break;

        case 'png':
            error_reporting(0);
            $this->image_url = $this->image->saveWebImage();
            $image_filename = basename($this->image_url);
            Header::set_header('png');
            header("Content-Disposition: attachment; filename=\"" . self::clean_filename($this->file_name, $this->output) . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($this->tmp_path.$image_filename));
            ob_clean();
            flush();
            readfile($this->tmp_path.$image_filename);
            break;

        case 'svg':
            Header::set_header('svg');
            header("Content-Disposition: attachment; filename=\"" . self::clean_filename($this->file_name, $this->output) . "\";");
            $this->image->saveImage("");
            break;

        default:
            Header::set_header('json');

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

            echo json_encode($output);
        }

    }

}