<?php

/**
 * Unit tests for static methods and set-up of MapprApi class
 */
class ApiTest extends PHPUnit_Framework_TestCase {

  protected $mappr_api;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->root = dirname(dirname(__FILE__));
    $this->mappr_api = new MapprApi();
    $this->mappr_api->set_shape_path($this->root."/lib/mapserver/maps")
        ->set_font_file($this->root."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path($this->root."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }
  
  protected function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
    $tmpfiles = glob($this->root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  public function test_api_ping() {
    $_REQUEST = array('ping' => true);
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertArrayHasKey("status", $output);
  }

  public function test_apioutput_post() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertArrayHasKey("imageURL", $output);
    $this->assertArrayHasKey("expiry", $output);
  }

  public function test_apioutput_get() {
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $image = imagecreatefromstring(ob_get_contents());
    ob_end_clean();
    $this->assertEquals(imagesx($image), 900);
    $this->assertEquals(imagesy($image), 450);
  }

  public function test_apioutput_get_params() {
    $_REQUEST = array(
      'bbox' => '-130,40,-60,50',
      'projection' => 'esri:102009',
      'width' => 600,
      'graticules' => true
    );
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $image = imagecreatefromstring(ob_get_contents());
    ob_end_clean();
    $this->assertEquals(imagesx($image), 600);
    $this->assertEquals(imagesy($image), 300);
  }
  
  public function test_apioutput_no_coords() {
    $_REQUEST = array(
      'points' => array()
    );
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $image = imagecreatefromstring(ob_get_contents());
    ob_end_clean();
    $this->assertEquals(imagesx($image), 900);
  }

  public function test_apioutput_coords() {
    $_REQUEST = array(
      'points' => array('45, -120\n52, -100')
    );
    $mappr_api = $this->mappr_api->get_request()->execute();
    ob_start();
    $output = $mappr_api->create_output();
    $image = imagecreatefromstring(ob_get_contents());
    ob_end_clean();
    $this->assertEquals(imagesx($image), 900);
  }

}
