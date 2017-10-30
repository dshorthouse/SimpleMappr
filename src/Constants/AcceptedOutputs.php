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
namespace SimpleMappr\Constants;

/**
 * Accepted output types for SimpleMappr
 *
 * @category  Trait
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
trait AcceptedOutputs
{
    /**
     * Acceptable outputs
     *
     * @var array $outputs
     */
    public static $outputs = [
      'png' => [
        "name" => "png",
        "driver" => "AGG/PNG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/png",
        "extension" => "png",
        "formatoptions" => ["INTERLACE=OFF", "COMPRESSION=9"]
      ],
      'pnga' => [
        "name" => "pnga",
        "driver" => "AGG/PNG",
        "imagemode" => MS_IMAGEMODE_RGBA,
        "mimetype" => "image/png",
        "extension" => "png",
        "transparent" => MS_TRUE,
        "formatoptions" => ["INTERLACE=OFF", "COMPRESSION=9"]
      ],
      'jpg' => [
        "name" => "jpg",
        "driver" => "AGG/JPEG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/jpeg",
        "extension" => "jpg",
        "formatoptions" => ["QUALITY=95"]
      ],
      'tif' => [
        "name" => "tif",
        "driver" => "GDAL/GTiff",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/tiff",
        "extension" => "tif",
        "formatoptions" => ["COMPRESS=JPEG", "JPEG_QUALITY=100", "PHOTOMETRIC=YCBCR"]
      ],
      'svg' => [
        "name" => "svg",
        "driver" => "CAIRO/SVG",
        "imagemode" => MS_IMAGEMODE_RGB,
        "mimetype" => "image/svg+xml",
        "extension" => "svg",
        "formatoptions" => ["COMPRESSED_OUTPUT=FALSE", "FULL_RESOLUTION=TRUE"]
      ]
    ];

    /**
     * Return array of accepted outputs.
     *
     * @return array of outputs.
     */
    public static function outputList()
    {
        return array_keys(self::$outputs);
    }
}
