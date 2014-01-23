<?php

/********************************************************************

utilities.class.php released under MIT License
Utility function for SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

class Utilities {

  public static function check_plain($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  public static function access_denied() {
    header("Content-Type: application/json");
    echo '{ "error" : "access denied" }';
    exit();
  }

  public static function set_header($mime = '') {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    switch($mime) {
      case '':
        break;

      case 'json':
        header("Content-Type: application/json");
        break;

      case 'html':
        header("Content-Type: text/html");
        break;

      case 'xml':
        header('Content-type: application/xml');
        break;

      case 'kml':
        header("Content-Type: application/vnd.google-earth.kml+xml kml; charset=utf8");
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
      case 'jpga':
        header("Content-Type: image/jpeg");
        break;

      case 'png':
      case 'pnga':
        header("Content-Type: image/png");
        break;

      default:
        header("Content-Type: image/png");
    }
  }

}

?>