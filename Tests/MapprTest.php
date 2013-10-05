<?php

require_once('DatabaseTest.php');

class MapprTest extends DatabaseTest {

    public function setUp() {
    }

    public function tearDown() {
    }
    
    public function test_add_slashes_extended() {
      
    }
    
    public function test_clean_filename() {
      $name = "My %!  <>  .  Map";
      $clean = Mappr::clean_filename($name);
      $this->assertEquals($clean, "My_Map");
    }
    
    public function test_make_coordinates() {
      $coord = '52° 32\' 25" N, 89° 40\' 31" W';
      $dd = Mappr::make_coordinates($coord);
      $this->assertEquals($dd[0], 52.540277777778);
      $this->assertEquals($dd[1], -89.675277777778);
    }

}
