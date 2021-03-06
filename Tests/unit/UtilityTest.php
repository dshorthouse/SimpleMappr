<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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

use PHPUnit\Framework\TestCase;
use SimpleMappr\Utility;

/**
 * Test Utility class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class UtilityTest extends TestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test that a hex color is properly converted to array of RGB.
     *
     * @return void
     */
    public function testHex()
    {
        $color = "#FF1177";
        $converted = Utility::hex2Rgb($color);
        $this->assertEquals([255, 17, 119], $converted);
    }

    /**
     * Test that a filename is cleaned of unrecognized characters.
     *
     * @return void
     */
    public function testFilename1()
    {
        $filename = "My@ New()*? ë  [Filename]'";
        $cleaned = Utility::cleanFilename($filename, "jpg");
        $this->assertEquals("My_New_ë_Filename_.jpg", $cleaned);
    }

    /**
     * Test that a filename is cleaned of unrecognized characters.
     *
     * @return void
     */
    public function testFilename2()
    {
        $filename = "My Map";
        $cleaned = Utility::cleanFilename($filename, "jpg");
        $this->assertEquals("My_Map.jpg", $cleaned);
    }

    /**
     * Test that a param is loaded.
     *
     * @return void
     */
    public function testLoadParam()
    {
        $req = [
            'points' => '45,-120'
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('points', '75,100');
        $this->assertEquals('45,-120', $points);
    }

    /**
     * Test that a default value for a param is loaded.
     *
     * @return void
     */
    public function testLoadParamDefault()
    {
        $req = [
            'stuff' => '45,-120'
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('points', '75,100');
        $this->assertEquals('75,100', $points);
    }

    /**
     * Test that slashes are added to param value.
     *
     * @return void
     */
    public function testLoadParamSlashes()
    {
        $req = [
            'stuff' => "here's Some \" Stuff on a \n new line"
        ];
        $this->setRequest($req);
        $points = Utility::loadParam('stuff', '');
        $this->assertEquals("here\'s Some \\\" Stuff on a \n new line", $points);
    }

    /**
     * Test that empty lines are removed from a value.
     *
     * @return void
     */
    public function testRemoveEmptyLines()
    {
        $text = "Here \n is \n\n some \n\n\n stuff";
        $cleaned = Utility::removeEmptyLines($text);
        $this->assertEquals("Here \n is \n some \n stuff", $cleaned);
    }
}
