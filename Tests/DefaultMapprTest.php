<?php

/**
 * Unit tests for static methods and default set-up of Mappr class
 */

class DefaultMapprTest extends PHPUnit_Framework_TestCase {

   private static $mappr;
   private static $output;

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
     ob_start();
     self::$mappr->get_output();
     self::$output = json_decode(ob_get_contents(), TRUE);
     ob_end_clean();
   }

   public static function tearDownAfterClass() {
     $root = dirname(dirname(__FILE__));
     $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
     foreach ($tmpfiles as $file) {
       unlink($file);
     }
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

    public function test_shape_path() {
      $root = dirname(dirname(__FILE__));
      $this->assertEquals(self::$mappr->get_shape_path(), $root."/lib/mapserver/maps");
    }

    public function test_font_file() {
      $root = dirname(dirname(__FILE__));
      $this->assertEquals(self::$mappr->get_font_file(), $root."/lib/mapserver/fonts/fonts.list");
    }

    public function test_tmp_path() {
      $root = dirname(dirname(__FILE__));
      $this->assertEquals(self::$mappr->get_tmp_path(), $root."/public/tmp/");
    }

    public function test_tmp_url() {
      $this->assertEquals(self::$mappr->get_tmp_url(), MAPPR_MAPS_URL);
    }

    public function test_max_extent() {
      $diff = array_diff(self::$mappr->get_max_extent(), [-180, -90, 180, 90]);
      $this->assertEmpty($diff);
    }

    public function test_default_projection() {
      $this->assertEquals(self::$mappr->get_default_projection(), "epsg:4326");
    }

    public function test_default_coords() {
      $this->assertEmpty(self::$mappr->coords);
    }

    public function test_default_regions() {
      $this->assertEmpty(self::$mappr->regions);
    }

    public function test_default_output() {
      $this->assertEquals(self::$mappr->output, "pnga");
    }

    public function test_default_width() {
      $this->assertEquals(self::$mappr->width, 900);
    }

    public function test_default_height() {
      $this->assertEquals(self::$mappr->height, 450);
    }

    public function test_default_projection_map() {
      $this->assertEquals(self::$mappr->projection_map, "epsg:4326");
    }

    public function test_default_origin() {
      $this->assertEquals(self::$mappr->origin, 0);
    }

    public function test_default_bbox_rubberband() {
      $this->assertEmpty(self::$mappr->bbox_rubberband);
    }

    public function test_default_pan() {
      $this->assertEquals(self::$mappr->pan, "");
    }

    public function test_default_layers() {
      $layers = self::$mappr->layers;
      $this->assertEmpty(array_diff($layers, array('base' => 'on')));
    }

    public function test_default_graticules() {
      $this->assertEquals(self::$mappr->graticules, "");
    }

    public function test_default_watermark() {
      $this->assertEquals(self::$mappr->watermark, "");
    }

    public function test_default_gridspace() {
      $this->assertEquals(self::$mappr->gridspace, "");
    }

    public function test_default_gridlabel() {
      $this->assertEquals(self::$mappr->gridlabel, 1);
    }

    public function test_default_download() {
      $this->assertEquals(self::$mappr->download, "");
    }

    public function test_default_crop() {
      $this->assertEquals(self::$mappr->crop, "");
    }

    public function test_default_options() {
      $this->assertEmpty(self::$mappr->options);
    }

    public function test_default_border_thickness() {
      $this->assertEquals(self::$mappr->border_thickness, 1.25);
    }

    public function test_default_rotation() {
      $this->assertEquals(self::$mappr->rotation, 0);
    }

    public function test_default_zoom_out() {
      $this->assertEquals(self::$mappr->zoom_out, "");
    }

    public function test_default_image_url() {
      $this->assertStringEndsWith(".png", self::$mappr->image_url);
    }

    public function test_mapserver_output_is_json() {
      $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
    }

    public function test_mapserver_output_contains_all_keys() {
      $this->assertArrayHasKey("mapOutputImage", self::$output);
      $this->assertArrayHasKey("size", self::$output);
      $this->assertArrayHasKey("rendered_bbox", self::$output);
      $this->assertArrayHasKey("rendered_rotation", self::$output);
      $this->assertArrayHasKey("rendered_projection", self::$output);
      $this->assertArrayHasKey("legend_url", self::$output);
      $this->assertArrayHasKey("scalebar_url", self::$output);
      $this->assertArrayHasKey("bad_points", self::$output);
    }

    public function test_file_exists() {
      $root = dirname(dirname(__FILE__));
      $this->assertFileExists($root . self::$output["mapOutputImage"]);
    }

    public function test_mapserver_default_size() {
      $diff = array_diff(self::$output["size"], [900, 450]);
      $this->assertEmpty($diff);
    }

    public function test_mapserver_default_rendered_bbox() {
      $this->assertEquals(self::$output["rendered_bbox"], "-180.0000000000,-90.0000000000,180.0000000000,90.0000000000");
    }

    public function test_mapserver_default_rendered_rotation() {
      $this->assertEquals(self::$output["rendered_rotation"], 0);
    }

    public function test_mapserver_rendered_projection() {
      $this->assertEquals(self::$output["rendered_projection"], "epsg:4326");
    }

    public function test_mapserver_default_bad_points() {
      $this->assertEquals(self::$output["bad_points"], "");
    }

}
