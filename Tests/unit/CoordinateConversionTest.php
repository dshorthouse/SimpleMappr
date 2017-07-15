<?php

/**
 * Unit tests for converting coordinates using static methods in Mappr class
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Utility;

class CoordinateConversionTest extends TestCase
{
    /**
     * Test that coordinates are cleaned of extraneous materials.
     */
    public function test_clean_coord()
    {
        $coord = "-45d.4dt5;0dds";
        $clean = Utility::cleanCoord($coord);
        $this->assertEquals($clean, -45.450);
    }

    /**
     * Test that off Earth coordinates are detected.
     */
    public function test_check_on_earth_invalid()
    {
        $coord = new stdClass();
        $coord->x = -133;
        $coord->y = 5543;
        $checked = Utility::onEarth($coord);
        $this->assertFalse($checked);
    }

    /**
     * Test that an on Earth coordinate is detected.
     */
    public function test_check_on_earth_valid()
    {
        $coord = new stdClass();
        $coord->x = -120;
        $coord->y = 43;
        $checked = Utility::onEarth($coord);
        $this->assertTrue($checked);
    }

    /**
     * Test that a partial coordinate is not converted.
     */
    public function test_make_coordinates_0()
    {
        $coord = "52° 32' 25\" N,";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate is not converted.
     */
    public function test_make_coordinates_1()
    {
        $coord = "-120";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate with a comma is not converted.
     */
    public function test_make_coordinates_2()
    {
        $coord = "-120,";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that well-formed coordinate in DMS with comma is converted.
     */
    public function test_make_coordinates_3()
    {
        $coord = "52° 32' 25\" N, 89° 40' 31\" W";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DMS with semicolon is converted.
     */
    public function test_make_coordinates_4()
    {
        $coord = "52° 32' 25\" N; 89° 40' 31\" W";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     */
    public function test_make_coordinates_5()
    {
        $coord = "52.5\t-89.0";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     */
    public function test_make_coordinates_6()
    {
        $coord = "-7.483333, - 36.283333";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], -7.483333);
        $this->assertEquals($dd[1], -36.283333);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     */
    public function test_make_coordinates_7()
    {
        $coord = " - 7.483333, - 36.283333";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], -7.483333);
        $this->assertEquals($dd[1], -36.283333);
    }

    /**
     * Test that flipped coordinate in DMS with comma is converted.
     */
    public function test_make_coordinates_8()
    {
        $coord = "89° 40' 31\" W, 52° 32' 25\" N";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }
    /**
     * Test that a single coordinate in DMS with 'd' is converted.
     */
    public function test_dms_to_deg_1()
    {
        $dms = "45d53'25\"W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with degree symbol is converted.
     */
    public function test_dms_to_deg_2()
    {
        $dms = "45° 53' 25\" W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with odd degree symbol is converted.
     */
    public function test_dms_to_deg_3()
    {
        $dms = "45º 53' 25\" W";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in East is converted.
     */
    public function test_dms_to_deg_4()
    {
        $dms = "45d53'25\"E";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS is North is converted.
     */
    public function test_dms_to_deg_5()
    {
        $dms = "45º 53′ 25″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in North with 'd' is converted.
     */
    public function test_dms_to_deg_6()
    {
        $dms = "45d 53m 25 N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in South with 'd' is converted.
     */
    public function test_dms_to_deg_7()
    {
        $dms = "45d53'25\"S";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with minutes > 60 is not converted.
     */
    public function test_dms_to_deg_8()
    {
        $dms = "45º 70′ 25″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that a single coordinate in DMS with seconds > 60 is not converted.
     */
    public function test_dms_to_deg_9()
    {
        $dms = "45º 40′ 85″ N";
        $dd = Utility::dmsToDeg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that two single quotes are used in DMS to indicate secs.
     */
    public function test_dms_to_deg_10()
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
     */
    public function test_dms_to_deg_11()
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
     */
    public function test_dms_to_deg_12()
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
     */
    public function test_dms_to_deg_13()
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
     */
    public function test_dms_to_deg_14()
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
     */
    public function test_dms_to_deg_15()
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
     */
    public function test_dirty_deg()
    {
        $coord = "52.5g\t-89.0r";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test DD with deg symbols.
     */
    public function test_dd_symbols()
    {
        $coord = " 49.129774°  46.677716°";
        $dd = Utility::makeCoordinates($coord);
        $this->assertEquals($dd[0], 49.129774);
        $this->assertEquals($dd[1], 46.677716);
    }
}
