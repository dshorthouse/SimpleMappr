<?php

require_once('DatabaseTest.php');

class MapprTest extends DatabaseTest {

   private static $mappr;
   private $output;

   public static function setUpBeforeClass() {
      $root = dirname(dirname(__FILE__));
      $mappr = new Mappr();
      $mappr->set_shape_path($root."/lib/mapserver/maps")
           ->set_font_file($root."/lib/mapserver/fonts/fonts.list")
           ->set_tmp_path($root."/public/tmp/")
           ->set_tmp_url(MAPPR_MAPS_URL)
           ->set_default_projection("epsg:4326")
           ->set_max_extent("-180,-90,180,90")
           ->get_request();
     self::$mappr = $mappr->execute();
   }

   public static function tearDownAfterClass() {
     $root = dirname(dirname(__FILE__));
     $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
     foreach ($tmpfiles as $file) {
       unlink($file);
     }
   }

   public function setUp() {
     self::$mappr->get_output();
     $this->output = json_decode(ob_get_contents(), TRUE);
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
      $this->assertFalse(self::$mappr->has_error());
    }

    public function test_mapserver_output_is_json() {
      
      $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
    }

    public function test_mapserver_output_contains_all_keys() {
      $this->assertArrayHasKey("mapOutputImage", $this->output);
      $this->assertArrayHasKey("size", $this->output);
      $this->assertArrayHasKey("rendered_bbox", $this->output);
      $this->assertArrayHasKey("rendered_rotation", $this->output);
      $this->assertArrayHasKey("rendered_projection", $this->output);
      $this->assertArrayHasKey("legend_url", $this->output);
      $this->assertArrayHasKey("scalebar_url", $this->output);
      $this->assertArrayHasKey("bad_points", $this->output);
    }

    public function test_mapserver_default_output_size() {
      $diff = array_diff($this->output["size"], [900, 450]);
      $this->assertEmpty($diff);
    }

    public function test_mapserver_default_rendered_bbox() {
      $this->assertEquals($this->output["rendered_bbox"], "-180.0000000000,-90.0000000000,180.0000000000,90.0000000000");
    }

    public function test_mapserver_default_rotation() {
      $this->assertEquals($this->output["rendered_rotation"], 0);
    }

    public function test_mapserver_rendered_projection() {
      $this->assertEquals($this->output["rendered_projection"], "epsg:4326");
    }

    public function test_mapserver_default_bad_points() {
      $this->assertEquals($this->output["bad_points"], "");
    }

}
