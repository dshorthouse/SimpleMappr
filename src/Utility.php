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

use \ForceUTF8\Encoding;

/**
 * Utility methods for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Utility
{
    /**
     * Convert a string to HTML entities.
     *
     * @param string $text Some HTML that needs cleaning.
     *
     * @return string Cleaned string.
     */
    public static function checkPlain($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Throw a 404 and some JSON when access has been denied.
     *
     * @return void
     */
    public static function accessDenied()
    {
        Header::setHeader('json');
        http_response_code(401);
        echo json_encode(["error" => "access denied"]);
        exit();
    }

    /**
     * Get a case insensitive request parameter.
     *
     * @param string $name    Name of the parameter.
     * @param string $default Default value for the parameter.
     *
     * @return string The parameter value or empty string if null.
     */
    public static function loadParam($name, $default = "")
    {
        $grep_key = self::pregGrepKeys("/\b(?<!-)$name(?!-)\b/i", $_REQUEST);
        if (!$grep_key || !array_values($grep_key)[0]) {
            return $default;
        }
        $value = array_values($grep_key)[0];
        $value = Encoding::fixUTF8($value);
        if (get_magic_quotes_gpc() != 1) {
            $value = self::addSlashesExtended($value);
        }
        if ($value == "false") {
            $value = false;
        }
        if ($value == "true") {
            $value = true;
        }
        return $value;
    }

    /**
     * Add slashes to either a string or an array
     *
     * @param string|array $arr_r String or array to add slashes
     *
     * @return string|array
     */
    public static function addSlashesExtended(&$arr_r)
    {
        if ((array)$arr_r === $arr_r) {
            foreach ($arr_r as &$val) {
                ((array)$val === $val) ? self::addSlashesExtended($val) : $val = addslashes($val);
            }
            unset($val);
        } else {
            $arr_r = addslashes($arr_r);
        }
        return $arr_r;
    }

    /**
     * Parse the configured URL to the application in the config
     *
     * @return array
     */
    public static function parsedURL()
    {
        return parse_url(MAPPR_URL);
    }

    /**
     * Remove empty lines from a string.
     *
     * @param string $text String of characters
     *
     * @return string cleansed string with empty lines removed
     */
    public static function removeEmptyLines($text)
    {
        return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
    }

    /**
     * Get a user-defined file name, cleaned of illegal characters.
     *
     * @param string $file_name String that should be a file name.
     * @param string $extension File extension.
     *
     * @return string Cleaned string that can be a file name.
     */
    public static function cleanFilename($file_name, $extension = "")
    {
        $clean_filename = preg_replace("/[^\w\-]+/u", '_', $file_name);
        if ($extension) {
            return $clean_filename . "." . $extension;
        }
        return $clean_filename;
    }

    /**
     * Grep on array keys.
     *
     * @param string $pattern A regex.
     * @param array  $input   An associative array.
     * @param int    $flags   Preg grep flags.
     *
     * @return array of matched keys.
     */
    public static function pregGrepKeys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Convert hex colour (eg for css) to RGB
     *
     * @param string $hex The hexidecimal string for the colour.
     *
     * @return array of RGB
     */
    public static function hex2Rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        if (strlen($hex) == 3) {
            $red = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $green = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $blue = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        }
        return [$red, $green, $blue];
    }

    /**
     * Split DDMMSS or DD coordinate pair string into an array
     *
     * @param string $point A string purported to be a coordinate
     *
     * @return array [latitude, longitude] in DD
     */
    public static function makeCoordinates($point)
    {
        $loc = preg_replace(["/[\p{Z}\s]/u", "/[^\d\s,;.\-NSEWO°ºdms'\"]/i", "/-\s+(?=\d)/"], [" ", "", "-"], $point);
        if (preg_match("/[NSEWO]/", $loc) != 0) {
            $coord = preg_split("/[,;]/", $loc); //split by comma or semicolon
            if (count($coord) != 2 || empty($coord[1])) {
                return [null, null];
            }
            $coord = (preg_match("/[EWO]/", $coord[1]) != 0) ? $coord : array_reverse($coord);
            return [self::dmsToDeg(trim($coord[0])),self::dmsToDeg(trim($coord[1]))];
        } else {
            $coord = preg_split("/[\s,;]+/", trim(preg_replace("/[^0-9-\s,;.]/", "", $loc)));
            if (count($coord) != 2 || empty($coord[1])) {
                return [null, null];
            }
            return $coord;
        }
    }

    /**
     * Convert a coordinate in dms to deg
     *
     * @param string $dms coordinate
     *
     * @return float
     */
    public static function dmsToDeg($dms)
    {
        $dec = null;
        $dms = stripslashes($dms);
        $neg = (preg_match('/[SWO]/', $dms) == 0) ? 1 : -1;
        $dms = preg_replace('/(^\s?-)|(\s?[NSEWO]\s?)/', "", $dms);
        $pattern = "/(\\d*\\.?\\d+)(?:[°ºd: ]+)(\\d*\\.?\\d+)*(?:['m′: ])*(\\d*\\.?\\d+)*[\"s″ ]?/";
        $parts = preg_split($pattern, $dms, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (!$parts) {
            return;
        }
        // parts: 0 = degree, 1 = minutes, 2 = seconds
        $deg = isset($parts[0]) ? (float)$parts[0] : 0;
        $min = isset($parts[1]) ? (float)$parts[1] : 0;
        if (strpos($dms, ".") > 1 && isset($parts[2])) {
            $min = (float)($parts[1] . '.' . $parts[2]);
            unset($parts[2]);
        }
        $sec = isset($parts[2]) ? (float)$parts[2] : 0;
        if ($min >= 0 && $min < 60 && $sec >= 0 && $sec < 60) {
            $dec = ($deg + ($min/60) + ($sec/3600))*$neg;
        }
        return $dec;
    }

    /**
     * Clean extraneous materials in coordinate that should (in theory) be DD.
     *
     * @param string $coord Dirty string that should be a real number
     *
     * @return float Cleaned coordinate
     */
    public static function cleanCoord($coord)
    {
        return preg_replace("/[^\d.-]+/", "", $coord);
    }

    /**
     * Check a DD coordinate object and return true if it fits on globe, false if not
     *
     * @param obj $coord (x,y) coordinates
     *
     * @return bool
     */
    public static function onEarth($coord)
    {
        if ($coord->x
            && $coord->y
            && is_numeric($coord->x)
            && is_numeric($coord->y)
            && $coord->y <= 90
            && $coord->y >= -90
            && $coord->x <= 180
            && $coord->x >= -180
        ) {
            return true;
        }
        return false;
    }
}
