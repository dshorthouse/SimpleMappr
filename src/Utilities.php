<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
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
     * Get a request parameter.
     *
     * @param string $name    Parameter name.
     * @param string $default Default when parameter not supplied.
     *
     * @return string The parameter value or empty string if null.
     */
    public static function loadParam($name, $default = "")
    {
        if (!isset($_REQUEST[$name]) || !$_REQUEST[$name]) {
            return $default;
        }
        $value = $_REQUEST[$name];
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

}