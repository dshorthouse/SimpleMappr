<?php

/**
 * Unit tests for static methods and set-up of MapprApi class
 */

class ApiTest extends PHPUnit_Framework_TestCase {

  protected $mappr_api;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $root = dirname(dirname(__FILE__));
    $this->mappr_api = new MapprApi();
    $this->mappr_api->set_shape_path($root."/lib/mapserver/maps")
        ->set_font_file($root."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path($root."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }
  
  protected function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }
  
  public function test_apioutput_post() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $mappr_api->get_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertArrayHasKey("imageURL", $output);
    $this->assertArrayHasKey("expiry", $output);
  }


  public function test_apioutput_get() {
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $mappr_api->get_output();
    $output = ob_get_contents();
    ob_end_clean();
    $image = imagecreatefromstring($output);
    $this->assertEquals(imagesx($image), 900);
  }

}

