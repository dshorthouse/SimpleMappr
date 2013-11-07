<?php

/**
 * Unit tests for converting coordinates using static methods in Mappr class
 */

class CoordinateConversionTest extends PHPUnit_Framework_TestCase {

  public function test_clean_coord() {
    $coord = "-45d.4dt5;0dds";
    $clean = Mappr::clean_coord($coord);
    $this->assertEquals($clean, -45.450);
  }
  
  public function test_check_coord_invalid() {
    $coord = new stdClass();
    $coord->x = -133;
    $coord->y = 5543;
    $checked = Mappr::check_coord($coord);
    $this->assertFalse($checked);
  }

  public function test_check_coord_valid() {
    $coord = new stdClass();
    $coord->x = -120;
    $coord->y = 43;
    $checked = Mappr::check_coord($coord);
    $this->assertTrue($checked);
  }

  public function test_make_coordinates_1() {
    $coord = "52° 32' 25\" N, 89° 40' 31\" W";
    $dd = Mappr::make_coordinates($coord);
    $this->assertEquals($dd[0], 52.540277777778);
    $this->assertEquals($dd[1], -89.675277777778);
  }

  public function test_make_coordinates_2() {
    $coord = "52° 32' 25\" N; 89° 40' 31\" W";
    $dd = Mappr::make_coordinates($coord);
    $this->assertEquals($dd[0], 52.540277777778);
    $this->assertEquals($dd[1], -89.675277777778);
  }

  public function test_make_coordinates_3() {
    $coord = "52.5\t-89.0";
    $dd = Mappr::make_coordinates($coord);
    $this->assertEquals($dd[0], 52.5);
    $this->assertEquals($dd[1], -89.0);
  }
  
  public function test_dms_to_deg_1() {
    $dms = "45d53'25\"W";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, -45.890277777778);
  }

  public function test_dms_to_deg_2() {
    $dms = "45° 53' 25\" W";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, -45.890277777778);
  }

  public function test_dms_to_deg_3() {
    $dms = "45º 53' 25\" W";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, -45.890277777778);
  }

  public function test_dms_to_deg_4() {
    $dms = "45d53'25\"E";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, 45.890277777778);
  }

  public function test_dms_to_deg_5() {
    $dms = "45º 53′ 25″ N";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, 45.890277777778);
  }

  public function test_dms_to_deg_6() {
    $dms = "45d 53m 25 N";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, 45.890277777778);
  }

  public function test_dms_to_deg_7() {
    $dms = "45d53'25\"S";
    $dd = Mappr::dms_to_deg($dms);
    $this->assertEquals($dd, -45.890277777778);
  }
  
  public function test_dirty_deg() {
    $coord = "52.5g\t-89.0r";
    $dd = Mappr::make_coordinates($coord);
    $this->assertEquals($dd[0], 52.5);
    $this->assertEquals($dd[1], -89.0);
  }

}
?>