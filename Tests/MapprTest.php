<?php

require_once('DatabaseTest.php');

class MapprTest extends DatabaseTest {

   protected $mappr;

   protected function setUp() {
     $root = dirname(dirname(__FILE__));
     $this->mappr = new Mappr();
     $this->mappr->set_shape_path($root."/lib/mapserver/maps")
          ->set_font_file($root."/lib/mapserver/fonts/fonts.list")
          ->set_tmp_path($root."/public/tmp/")
          ->set_tmp_url(MAPPR_MAPS_URL)
          ->set_default_projection("epsg:4326")
          ->set_max_extent("-180,-90,180,90")
          ->get_request();
   }
   
   public function tearDown() {
   }

    public function test_remove_empty_lines() {
      $data = "\n\n45.0\t-120.0\n\n\n\n\n55.0\t-110.0\n\n\n60.0 -100.0\n\n\n";
      $removed_lines = Mappr::remove_empty_lines($data);
      $this->assertEquals($removed_lines, "\n45.0\t-120.0\n55.0\t-110.0\n60.0 -100.0\n");
    }

    public function test_add_slashes_extended() {
      $data = array(array('title' => 'my "title"'));
      $add_slashes = Mappr::add_slashes_extended($data);
      $this->assertEquals($add_slashes[0]['title'], "my \\\"title\\\"");
    }

    public function test_clean_filename() {
      $name = "My %!  <>  .  Map";
      $clean = Mappr::clean_filename($name);
      $this->assertEquals($clean, "My_Map");
    }

    public function test_mapserver_enabled() {
      $this->assertFalse($this->mappr->has_error());
    }

    public function test_mapserver_output_is_json() {
      $output = $this->mappr->execute()->get_output();
      json_decode($output);
      $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
    }

}
