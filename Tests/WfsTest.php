<?php

/**
 * Unit tests for static methods and set-up of MapprWfs class
 */

class WfsTest extends PHPUnit_Framework_TestCase {

  protected $mappr_wfs;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $root = dirname(dirname(__FILE__));
    $this->mappr_wfs = new MapprWfs();
    $this->mappr_wfs->set_shape_path($root."/lib/mapserver/maps")
        ->set_font_file($root."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path($root."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }

  protected function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
  }

  public function test_GetCapabilities() {
    $mappr_wfs = $this->mappr_wfs->get_request()->make_service()->execute();
    ob_start();
    $mappr_wfs->get_output();
    $output = ob_get_contents();
    ob_end_clean();
    $xml = simplexml_load_string($output);
    $this->assertEquals('SimpleMappr Web Feature Service', $xml->Service->Title);
    $this->assertEquals(15, count($xml->FeatureTypeList->FeatureType));
  }

  public function test_GetFeature() {
    $_REQUEST = array(
      'REQUEST' => 'GetFeature',
      'TYPENAME' => 'lakes',
      'MAXFEATURES' => '10'
    );
    $mappr_wfs = $this->mappr_wfs->get_request()->make_service()->execute();
    ob_start();
    $mappr_wfs->get_output();
    $output = ob_get_contents();
    ob_end_clean();
    $xml = simplexml_load_string($output);
    $ns = $xml->getNamespaces(true);
    $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
  }

}

