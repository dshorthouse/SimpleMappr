<?php

/**
 * Unit tests for static methods and default set-up of Mappr class
 */

class MapprApplicationTest extends PHPUnit_Framework_TestCase {

   protected $mappr;
   protected $output;

   protected function setUp() {
      $mappr = new \SimpleMappr\MapprApplication();
      $mappr->set_shape_path(ROOT."/lib/mapserver/maps")
           ->set_font_file(ROOT."/lib/mapserver/fonts/fonts.list")
           ->set_tmp_path(ROOT."/public/tmp/")
           ->set_tmp_url(MAPPR_MAPS_URL)
           ->set_default_projection("epsg:4326")
           ->set_max_extent("-180,-90,180,90")
           ->get_request();
     $this->mappr = $mappr->execute();
     ob_start();
     $this->mappr->create_output();
     $this->output = json_decode(ob_get_contents(), TRUE);
     ob_end_clean();
   }

   protected function tearDown() {
     $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
     foreach ($tmpfiles as $file) {
       unlink($file);
     }
   }

    public function test_remove_empty_lines() {
      $data = "\n\n45.0\t-120.0\n\n\n\n\n55.0\t-110.0\n\n\n60.0 -100.0\n\n\n";
      $removed_lines = \SimpleMappr\Mappr::remove_empty_lines($data);
      $this->assertEquals($removed_lines, "\n45.0\t-120.0\n55.0\t-110.0\n60.0 -100.0\n");
    }

    public function test_add_slashes_extended() {
      $data = array(array('title' => 'my "title"'));
      $add_slashes = \SimpleMappr\Mappr::add_slashes_extended($data);
      $this->assertEquals($add_slashes[0]['title'], "my \\\"title\\\"");
    }

    public function test_clean_filename() {
      $name = "My %!  <>  .  Map";
      $clean = \SimpleMappr\Mappr::clean_filename($name);
      $this->assertEquals($clean, "My_Map");
    }

    public function test_shape_path() {
      $this->assertEquals($this->mappr->get_shape_path(), ROOT."/lib/mapserver/maps");
    }

    public function test_font_file() {
      $this->assertEquals($this->mappr->get_font_file(), ROOT."/lib/mapserver/fonts/fonts.list");
    }

    public function test_tmp_path() {
      $this->assertEquals($this->mappr->get_tmp_path(), ROOT."/public/tmp/");
    }

    public function test_tmp_url() {
      $this->assertEquals($this->mappr->get_tmp_url(), MAPPR_MAPS_URL);
    }

    public function test_max_extent() {
      $diff = array_diff($this->mappr->get_max_extent(), [-180, -90, 180, 90]);
      $this->assertEmpty($diff);
    }

    public function test_default_projection() {
      $this->assertEquals($this->mappr->get_default_projection(), "epsg:4326");
    }

    public function test_default_coords() {
      $this->assertEmpty($this->mappr->coords);
    }

    public function test_default_regions() {
      $this->assertEmpty($this->mappr->regions);
    }

    public function test_default_output() {
      $this->assertEquals($this->mappr->output, "pnga");
    }

    public function test_default_width() {
      $this->assertEquals($this->mappr->width, 900);
    }

    public function test_default_height() {
      $this->assertEquals($this->mappr->height, 450);
    }

    public function test_default_projection_map() {
      $this->assertEquals($this->mappr->projection_map, "epsg:4326");
    }

    public function test_default_origin() {
      $this->assertEquals($this->mappr->origin, 0);
    }

    public function test_default_bbox_rubberband() {
      $this->assertEmpty($this->mappr->bbox_rubberband);
    }

    public function test_default_pan() {
      $this->assertEquals($this->mappr->pan, "");
    }

    public function test_default_layers() {
      $layers = $this->mappr->layers;
      $this->assertEmpty(array_diff($layers, array('base' => 'on')));
    }

    public function test_default_graticules() {
      $this->assertEquals($this->mappr->graticules, "");
    }

    public function test_default_watermark() {
      $this->assertEquals($this->mappr->watermark, "");
    }

    public function test_default_gridspace() {
      $this->assertEquals($this->mappr->gridspace, "");
    }

    public function test_default_gridlabel() {
      $this->assertEquals($this->mappr->gridlabel, 1);
    }

    public function test_default_download() {
      $this->assertEquals($this->mappr->download, "");
    }

    public function test_default_crop() {
      $this->assertEquals($this->mappr->crop, "");
    }

    public function test_default_options() {
      $this->assertEmpty($this->mappr->options);
    }

    public function test_default_border_thickness() {
      $this->assertEquals($this->mappr->border_thickness, 1.25);
    }

    public function test_default_rotation() {
      $this->assertEquals($this->mappr->rotation, 0);
    }

    public function test_default_zoom_out() {
      $this->assertEquals($this->mappr->zoom_out, "");
    }

    public function test_default_image_url() {
      $this->assertStringEndsWith(".png", $this->mappr->image_url);
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
    
    public function test_scalebar_url_exists() {
      $this->assertNotEmpty($this->output["scalebar_url"]);
    }
    
    public function test_legend_url_empty() {
      $this->assertEmpty($this->output["legend_url"]);
    }

    public function test_file_exists() {
      $img = $this->mappr->get_tmp_path() . basename($this->output["mapOutputImage"]);
      $this->assertFileExists($img);
    }

    public function test_mapserver_default_size() {
      $diff = array_diff($this->output["size"], [900, 450]);
      $this->assertEmpty($diff);
    }

    public function test_mapserver_default_rendered_bbox() {
      $this->assertEquals($this->output["rendered_bbox"], "-180.0000000000,-90.0000000000,180.0000000000,90.0000000000");
    }

    public function test_mapserver_default_rendered_rotation() {
      $this->assertEquals($this->output["rendered_rotation"], 0);
    }

    public function test_mapserver_rendered_projection() {
      $this->assertEquals($this->output["rendered_projection"], "epsg:4326");
    }

    public function test_mapserver_default_bad_points() {
      $this->assertEquals($this->output["bad_points"], "");
    }

}
