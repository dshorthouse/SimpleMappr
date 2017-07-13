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
 *
 */
namespace SimpleMappr;

use CssMin;

/**
 * HTTP Header handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Header
{
    /**
     * Set the HTTP response headers
     *
     * @param string $mime     Shortcut for the mimetype
     * @param string $filename The filename requested in a download
     * @param string $filesize The filesize
     *
     * @return void
     */
    public static function setHeader($mime = "", $filename = "", $filesize = "")
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        if ($filename) {
            header("Content-Disposition: attachment; filename=\"{$filename}\";");
        }
        if ($filesize) {
            header("Content-Length: {$filesize}");
        }
        switch ($mime) {
        case "":
            break;

        case 'json':
            header("Content-Type: application/json; charset=UTF-8");
            break;

        case 'html':
            header("Content-Type: text/html; charset=UTF-8");
            break;

        case 'xml':
            header('Content-type: application/xml');
            break;

        case 'kml':
            header("Content-Type: application/vnd.google-earth.kml+xml kml; charset=UTF-8");
            break;

        case 'pptx':
            header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'docx':
            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'tif':
            header("Content-Type: image/tiff");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'svg':
            header("Content-Type: image/svg+xml");
            break;

        case 'jpg':
            header("Content-Type: image/jpeg");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'png':
            header("Content-Type: image/png");
            header("Content-Transfer-Encoding: binary");
            break;

        default:
            header("Content-Type: image/png");
            header("Content-Transfer-Encoding: binary");
        }
    }
}
