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
 * Test coordinate conversion for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class CoordinateConversionTest extends TestCase
{
    /**
     * Test that coordinates are cleaned of extraneous materials.
     *
     * @return void
     */
    public function testCleanCoord()
    {
        $coord = "-45d.4dt5;0dds";
        $clean = Utility::cleanCoord($coord);
        $this->assertEquals($clean, -45.450);
    }

    /**
     * Test that off Earth coordinates are detected.
     *
     * @return void
     */
    public function testCheckOnEarthInvalid()
    {
        $coord = new stdClass();
        $coord->x = -133;
        $coord->y = 5543;
        $checked = Utility::onEarth($coord);
        $this->assertFalse($checked);
    }

    /**
     * Test that an on Earth coordinate is detected.
     *
     * @return void
     */
    public function testCheckOnEarthValid()
    {
        $coord = new stdClass();
        $coord->x = -120;
        $coord->y = 43;
        $checked = Utility::onEarth($coord);
        $this->assertTrue($checked);
    }

    /**
     * Test that a partial coordinate is not converted.
     *
     * @return void
     */
    public function testMakeCoordinates0()
    {
        $coord = "52° 32' 25\" N,";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate is not converted.
     *
     * @return void
     */
    public function testMakeCoordinates1()
    {
        $coord = "-120";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate with a comma is not converted.
     *
     * @return void
     */
    public function testMakeCoordinates2()
    {
        $coord = "-120,";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that well-formed coordinate in DMS with comma is converted.
     *
     * @return void
     */
    public function testMakeCoordinates3()
    {
        $coord = "52° 32' 25\" N, 89° 40' 31\" W";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DMS with semicolon is converted.
     *
     * @return void
     */
    public function testMakeCoordinates4()
    {
        $coord = "52° 32' 25\" N; 89° 40' 31\" W";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     *
     * @return void
     */
    public function testMakeCoordinates5()
    {
        $coord = "52.5\t-89.0";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     *
     * @return void
     */
    public function testMakeCoordinates6()
    {
        $coord = "-7.483333, - 36.283333";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], -7.483333);
        $this->assertEquals($dd[1], -36.283333);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     *
     * @return void
     */
    public function testMakeCoordinates7()
    {
        $coord = " - 7.483333, - 36.283333";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], -7.483333);
        $this->assertEquals($dd[1], -36.283333);
    }

    /**
     * Test that flipped coordinate in DMS with comma is converted.
     *
     * @return void
     */
    public function testMakeCoordinates8()
    {
        $coord = "89° 40' 31\" W, 52° 32' 25\" N";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }
    /**
     * Test that a single coordinate in DMS with 'd' is converted.
     *
     * @return void
     */
    public function testDmsToDeg1()
    {
        $dms = "45d53'25\"W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with degree symbol is converted.
     *
     * @return void
     */
    public function testDmsToDeg2()
    {
        $dms = "45° 53' 25\" W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with odd degree symbol is converted.
     *
     * @return void
     */
    public function testDmsToDeg3()
    {
        $dms = "45º 53' 25\" W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in East is converted.
     *
     * @return void
     */
    public function testDmsToDeg4()
    {
        $dms = "45d53'25\"E";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS is North is converted.
     *
     * @return void
     */
    public function testDmsToDeg5()
    {
        $dms = "45º 53′ 25″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in North with 'd' is converted.
     *
     * @return void
     */
    public function testDmsToDeg6()
    {
        $dms = "45d 53m 25 N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in South with 'd' is converted.
     *
     * @return void
     */
    public function testDmsToDeg7()
    {
        $dms = "45d53'25\"S";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with minutes > 60 is not converted.
     *
     * @return void
     */
    public function testDmsToDeg8()
    {
        $dms = "45º 70′ 25″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that a single coordinate in DMS with seconds > 60 is not converted.
     *
     * @return void
     */
    public function testDmsToDeg9()
    {
        $dms = "45º 40′ 85″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that two single quotes are used in DMS to indicate secs.
     *
     * @return void
     */
    public function testDmsToDeg10()
    {
        $dms = "03º23'45''S";
        $deg = 3;
        $min = 23/60;
        $sec = 45/3600;
        $dd_raw = -($deg + $min + $sec);
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -3.3958333333333);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that ddmmss with s in coord string is recognized as secs.
     *
     * @return void
     */
    public function testDmsToDeg11()
    {
        $dms = "44d53m23sN";
        $deg = 44;
        $min = 53/60;
        $sec = 23/3600;
        $dd_raw = $deg + $min + $sec;
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 44.889722222222218);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that ddmmss with s in coord string is recognized as secs.
     *
     * @return void
     */
    public function testDmsToDeg12()
    {
        $dms = "44d53m23sS";
        $deg = 44;
        $min = 53/60;
        $sec = 23/3600;
        $dd_raw = -($deg + $min + $sec);
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -44.889722222222218);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that ddmmss with s in coord string is recognized as secs.
     *
     * @return void
     */
    public function testDmsToDeg13()
    {
        $dms = "44d53m23sW";
        $deg = 44;
        $min = 53/60;
        $sec = 23/3600;
        $dd_raw = -($deg + $min + $sec);
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -44.889722222222218);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that ddmmss with s in coord string is recognized as secs.
     *
     * @return void
     */
    public function testDmsToDeg14()
    {
        $dms = "44d53m23sE";
        $deg = 44;
        $min = 53/60;
        $sec = 23/3600;
        $dd_raw = $deg + $min + $sec;
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 44.889722222222218);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that ddmmss with s in coord string is recognized as secs.
     *
     * @return void
     */
    public function testDmsToDeg15()
    {
        $dms = "44d 53m 23s W";
        $deg = 44;
        $min = 53/60;
        $sec = 23/3600;
        $dd_raw = -($deg + $min + $sec);
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -44.889722222222218);
        $this->assertEquals($dd, $dd_raw);
    }

    /**
     * Test that a dirty coordinate in DD is parsed.
     *
     * @return void
     */
    public function testDirtyDeg()
    {
        $coord = "52.5g\t-89.0r";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test DD with deg symbols.
     *
     * @return void
     */
    public function testDdSymbols()
    {
        $coord = " 49.129774°  46.677716°";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 49.129774);
        $this->assertEquals($dd[1], 46.677716);
    }
}
