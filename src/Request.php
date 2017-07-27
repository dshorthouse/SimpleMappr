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
namespace SimpleMappr;

/**
 * Default Request for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Request
{
    /**
     * Produce the default attributes for a request object
     *
     * @return object
     */
    public static function getRequest()
    {
        $attr = new \stdClass();
        $attr->coords           = Utility::loadParam('coords', []);
        $attr->regions          = Utility::loadParam('regions', []);
        $attr->wkt              = Utility::loadParam('wkt', []);
        $attr->output           = Utility::loadParam('output', 'png');
        $attr->width            = (float)Utility::loadParam('width', 900);
        $attr->height           = (float)Utility::loadParam('height', $attr->width/2);
        $attr->projection       = Utility::loadParam('projection', 'epsg:4326');
        $attr->projection_map   = Utility::loadParam('projection_map', 'epsg:4326');
        $attr->origin           = (int)Utility::loadParam('origin', false);
        $attr->bbox_map         = Utility::loadParam('bbox_map', '-180,-90,180,90');
        $attr->bbox_rubberband  = Utility::loadParam('bbox_rubberband', []);
        $attr->pan              = Utility::loadParam('pan', false);
        $attr->layers           = Utility::loadParam('layers', []);
        $attr->graticules       = (array_key_exists('grid', $attr->layers)) ? true : false;
        $attr->watermark        = Utility::loadParam('watermark', false);
        $attr->gridspace        = Utility::loadParam('gridspace', false);
        $attr->hide_gridlabel   = Utility::loadParam('hide_gridlabel', false);
        $attr->download         = Utility::loadParam('download', false);
        $attr->crop             = Utility::loadParam('crop', false);
        $attr->options          = Utility::loadParam('options', []);
        $attr->border_thickness = (float)Utility::loadParam('border_thickness', 1.25);
        $attr->rotation         = (int)Utility::loadParam('rotation', 0);
        $attr->zoom_in          = Utility::loadParam('zoom_in', false);
        $attr->zoom_out         = Utility::loadParam('zoom_out', false);
        $attr->download_factor  = (int)Utility::loadParam('download_factor', 1);
        $attr->file_name        = Utility::loadParam('file_name', time());
        $attr->download_token   = Utility::loadParam('download_token', md5(time()));
        setcookie("fileDownloadToken", $attr->download_token, time()+3600, "/");

        return $attr;
    }
}
