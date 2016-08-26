<?php

/**
 * AcceptedOutputs trait
 *
 * PHP Version >= 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2016 David P. Shorthouse
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
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

trait AcceptedOutputs
{
    /**
     * Acceptable outputs
     */
    public static $outputs = array(
      'png' => array(
        "name" => "png",
        "driver" => "AGG/PNG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/png",
        "extension" => "png",
        "formatoptions" => array("INTERLACE=OFF", "COMPRESSION=9")
      ),
      'pnga' => array(
        "name" => "pnga",
        "driver" => "AGG/PNG",
        "imagemode" => MS_IMAGEMODE_RGBA,
        "mimetype" => "image/png",
        "extension" => "png",
        "transparent" => MS_TRUE,
        "formatoptions" => array("INTERLACE=OFF", "COMPRESSION=9")
      ),
      'jpg' => array(
        "name" => "jpg",
        "driver" => "AGG/JPEG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/jpeg",
        "extension" => "jpg",
        "formatoptions" => array("QUALITY=95")
      ),
      'tif' => array(
        "name" => "tif",
        "driver" => "GDAL/GTiff",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/tiff",
        "extension" => "tif",
        "formatoptions" => array("COMPRESS=JPEG", "JPEG_QUALITY=100", "PHOTOMETRIC=YCBCR")
      ),
      'svg' => array(
        "name" => "svg",
        "driver" => "CAIRO/SVG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/svg+xml",
        "extension" => "svg",
        "formatoptions" => array("COMPRESSED_OUTPUT=FALSE", "FULL_RESOLUTION=TRUE")
      ),
    );
    
    public static function outputList()
    {
      return array_keys(self::$outputs);
    }
}