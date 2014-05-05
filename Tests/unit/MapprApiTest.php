<?php

/**
 * Unit tests for static methods and set-up of MapprApi class
 */

class MapprApiTest extends PHPUnit_Framework_TestCase {

  protected $mappr_api;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->mappr_api = new \SimpleMappr\MapprApi();
    $this->mappr_api->set_shape_path(ROOT."/lib/mapserver/maps")
        ->set_font_file(ROOT."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path(ROOT."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }
  
  protected function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
    $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  public function test_api_ping() {
    $_REQUEST = array('ping' => true);
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $decoded = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertArrayHasKey("status", $decoded);
  }

  public function test_apioutput_post() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $decoded = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertArrayHasKey("imageURL", $decoded);
    $this->assertArrayHasKey("expiry", $decoded);
  }

  public function test_apioutput_get() {
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $output = ob_get_contents();
    $file = ROOT.'/public/tmp/apioutput_get.png';
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/apioutput_get.png'));
  }

  public function test_apioutput_get_params() {
    $_REQUEST = array(
      'bbox' => '-130,40,-60,50',
      'projection' => 'esri:102009',
      'width' => 600,
      'graticules' => true
    );
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $output = ob_get_contents();
    $file = ROOT.'/public/tmp/apioutput_get_params.png';
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/apioutput_get_params.png'));
  }
  
  public function test_apioutput_no_coords() {
    $_REQUEST = array(
      'points' => array()
    );
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $output = ob_get_contents();
    $file = ROOT.'/public/tmp/apioutput_no_coords.png';
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/apioutput_no_coords.png'));
  }

  public function test_apioutput_coords() {
    $_REQUEST = array(
      'points' => array("45, -120\n52, -100")
    );
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $output = ob_get_contents();
    $file = ROOT.'/public/tmp/apioutput_coords.png';
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/apioutput_coords.png'));
  }

  public function test_apioutput_country() {
    $_REQUEST = array(
      'shade' => array(
        'places' => 'Alberta,USA[MT|WA]'
      )
    );
    $this->mappr_api->get_request()->execute();
    ob_start();
    $this->mappr_api->create_output();
    $output = ob_get_contents();
    $file = ROOT.'/public/tmp/apioutput_places.png';
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/apioutput_places.png'));
  }
}
