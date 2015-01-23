<?php

/**
 * Unit tests for converting coordinates using static methods in Mappr class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class CoordinateConversionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that coordinates are cleaned of extraneous materials.
     */
    public function test_clean_coord()
    {
        $coord = "-45d.4dt5;0dds";
        $clean = \SimpleMappr\Mappr::clean_coord($coord);
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
        $checked = \SimpleMappr\Mappr::check_on_earth($coord);
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
        $checked = \SimpleMappr\Mappr::check_on_earth($coord);
        $this->assertTrue($checked);
    }

    /**
     * Test that a partial coordinate is not converted.
     */
    public function test_make_coordinates_0()
    {
        $coord = "52° 32' 25\" N,";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate is not converted.
     */
    public function test_make_coordinates_1()
    {
        $coord = "-120";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that a partial coordinate with a comma is not converted.
     */
    public function test_make_coordinates_2()
    {
        $coord = "-120,";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], null);
        $this->assertEquals($dd[1], null);
    }

    /**
     * Test that well-formed coordinate in DMS with comma is converted.
     */
    public function test_make_coordinates_3()
    {
        $coord = "52° 32' 25\" N, 89° 40' 31\" W";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DMS with semicolon is converted.
     */
    public function test_make_coordinates_4()
    {
        $coord = "52° 32' 25\" N; 89° 40' 31\" W";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], 52.540277777778);
        $this->assertEquals($dd[1], -89.675277777778);
    }

    /**
     * Test that a well-formed coordinate in DD with tab is parsed.
     */
    public function test_make_coordinates_5()
    {
        $coord = "52.5\t-89.0";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test that a single coordinate in DMS with 'd' is converted.
     */
    public function test_dms_to_deg_1()
    {
        $dms = "45d53'25\"W";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with degree symbol is converted.
     */
    public function test_dms_to_deg_2()
    {
        $dms = "45° 53' 25\" W";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with odd degree symbol is converted.
     */
    public function test_dms_to_deg_3()
    {
        $dms = "45º 53' 25\" W";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in East is converted.
     */
    public function test_dms_to_deg_4()
    {
        $dms = "45d53'25\"E";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS is North is converted.
     */
    public function test_dms_to_deg_5()
    {
        $dms = "45º 53′ 25″ N";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in North with 'd' is converted.
     */
    public function test_dms_to_deg_6()
    {
        $dms = "45d 53m 25 N";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, 45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS in South with 'd' is converted.
     */
    public function test_dms_to_deg_7()
    {
        $dms = "45d53'25\"S";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, -45.890277777778);
    }

    /**
     * Test that a single coordinate in DMS with minutes > 60 is not converted.
     */
    public function test_dms_to_deg_8()
    {
        $dms = "45º 70′ 25″ N";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that a single coordinate in DMS with seconds > 60 is not converted.
     */
    public function test_dms_to_deg_9()
    {
        $dms = "45º 40′ 85″ N";
        $dd = \SimpleMappr\Mappr::dms_to_deg($dms);
        $this->assertEquals($dd, null);
    }

    /**
     * Test that a dirty coordinate in DD is parsed.
     */
    public function test_dirty_deg()
    {
        $coord = "52.5g\t-89.0r";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], 52.5);
        $this->assertEquals($dd[1], -89.0);
    }

    /**
     * Test DD with deg symbols.
     */
    public function test_dd_symbols()
    {
        $coord = " 49.129774°  46.677716°";
        $dd = \SimpleMappr\Mappr::make_coordinates($coord);
        $this->assertEquals($dd[0], 49.129774);
        $this->assertEquals($dd[1], 46.677716);
    }

}