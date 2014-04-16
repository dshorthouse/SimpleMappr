<?php

/**
 * Unit tests for static methods and set-up of MapprQuery class
 */
class QueryTest extends PHPUnit_Framework_TestCase {

  protected $mappr_query;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $root = dirname(dirname(__FILE__));
    $this->mappr_query = new MapprQuery();
    $this->mappr_query->set_shape_path($root."/lib/mapserver/maps")
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

  public function testCountry() {
    $_REQUEST['bbox_query'] = '176,83,176,83';
    $this->mappr_query->get_request()->execute()->query_layer();
    ob_start();
    $this->mappr_query->create_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertEquals('Canada', $output[0]);
  }

  public function testManyCountries() {
    $_REQUEST['bbox_query'] = '786,272,900,358';
    $this->mappr_query->get_request()->execute()->query_layer();
    ob_start();
    $this->mappr_query->create_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertTrue(in_array("Australia",$output));
    $this->assertTrue(in_array("New Zealand",$output));
  }

  public function testStateProvince() {
    $_REQUEST['bbox_query'] = '176,83,176,83';
    $_REQUEST['qlayer'] = 'stateprovinces_polygon';
    $this->mappr_query->get_request()->execute()->query_layer();
    ob_start();
    $this->mappr_query->create_output();
    $output = json_decode(ob_get_contents(), TRUE);
    ob_end_clean();
    $this->assertEquals('CAN[SK]', $output[0]);
  }
}

?>