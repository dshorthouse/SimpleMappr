<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
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
 * Utilities for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Utilities
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
        echo json_encode(array("error" => "access denied"));
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
        return $value;
    }

    /**
     * Add slashes to either a string or an array
     *
     * @param string $arr_r String or array to add slashes
     *
     * @return string/array
     */
    public static function addSlashesExtended(&$arr_r)
    {
        if (is_array($arr_r)) {
            foreach ($arr_r as &$val) {
                is_array($val) ? self::addSlashesExtended($val) : $val = addslashes($val);
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
        $clean_filename = preg_replace("/[?*:;{}\\ \"'\/@#!%^()<>.]+/", "_", $file_name);
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
        return array($red, $green, $blue);
    }

}