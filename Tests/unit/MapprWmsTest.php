<?php

/**
 * Unit tests for static methods and set-up of MapprWms class
 */

class MapprWmsTest extends PHPUnit_Framework_TestCase {

  protected $mappr_wms;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->mappr_wms = new \SimpleMappr\MapprWms();
    $this->mappr_wms->wms_layers = array(
      'lakes' => 'on',
      'stateprovinces_polygon' => 'on'
    );
    $this->mappr_wms->set_shape_path(ROOT."/lib/mapserver/maps")
        ->set_font_file(ROOT."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path(ROOT."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }

  protected function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
  }

  public function test_GetCapabilities() {
    $mappr_wms = $this->mappr_wms->get_request()->make_service()->execute();
    ob_start();
    $mappr_wms->create_output();
    $xml = simplexml_load_string(ob_get_contents());
    ob_end_clean();
    $this->assertEquals('SimpleMappr Web Map Service', $xml->Service->Title);
    $this->assertEquals(3, count($xml->Capability->Layer->Layer));
  }

  public function test_GetMap() {
    $_REQUEST = array(
      'REQUEST' => 'GetMap',
      'LAYERS' => 'lakes',
      'BBOX' => '-120,45,-70,70',
      'SRS' => 'epsg:4326',
      'WIDTH' => 400,
      'HEIGHT' => 200
    );
    $mappr_wms = $this->mappr_wms->get_request()->make_service()->execute();
    ob_start();
    $mappr_wms->create_output();
    $image = imagecreatefromstring(ob_get_contents());
    ob_end_clean();
    $this->assertEquals(imagesx($image), 400);
    $this->assertEquals(imagesy($image), 200);
  }
  
  public function test_CaseInsensitiveRequest() {
    $_REQUEST = array(
      'request' => 'GetMap',
      'layers' => 'lakes',
      'bbox' => '-120,45,-70,70',
      'srs' => 'epsg:4326',
      'width' => 400,
      'height' => 200
    );
    $mappr_wms = $this->mappr_wms->get_request();
    $this->assertEquals($this->mappr_wms->params['REQUEST'], $_REQUEST['request']);
    $this->assertEquals($this->mappr_wms->params['LAYERS'], $_REQUEST['layers']);
    $this->assertEquals($this->mappr_wms->params['BBOX'], $_REQUEST['bbox']);
    $this->assertEquals($this->mappr_wms->params['SRS'], $_REQUEST['srs']);
    $this->assertEquals($this->mappr_wms->params['WIDTH'], $_REQUEST['width']);
    $this->assertEquals($this->mappr_wms->params['HEIGHT'], $_REQUEST['height']);
    $this->assertEquals($this->mappr_wms->params['VERSION'], '1.1.1');
    $this->assertEquals($this->mappr_wms->params['FORMAT'], 'image/png');
  }

}