<?php

/**
 * Unit tests for static methods and set-up of MapprMap class
 */

class MapTest extends SimpleMapprTest {

  protected $mappr_map;

  public function setUp() {
    $this->mappr_map = "";
  }

  public function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
    $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml,json,svg}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  private function setUpMap($ext = "png") {
    $this->mappr_map = new \SimpleMappr\MapprMap(1, $ext);
    $this->mappr_map->set_shape_path(ROOT."/lib/mapserver/maps")
        ->set_font_file(ROOT."/lib/mapserver/fonts/fonts.list")
        ->set_tmp_path(ROOT."/public/tmp/")
        ->set_tmp_url(MAPPR_MAPS_URL)
        ->set_default_projection("epsg:4326")
        ->set_max_extent("-180,-90,180,90");
  }

  public function test_map_png() {
    $this->setUpMap();
    $this->mappr_map->get_request()->execute();
    ob_start();
    $this->mappr_map->create_output();
    $output = ob_get_contents();
    $file = ROOT."/public/tmp/map_png.png";
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/map_png.png'));
  }

  public function test_map_json() {
    $this->setUpMap('json');
    $this->mappr_map->get_request()->execute();
    ob_start();
    $this->mappr_map->create_output();
    $output = ob_get_contents();
    $file = ROOT."/public/tmp/map_json.json";
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/map_json.json'));
  }

  public function test_map_kml() {
    $this->setUpMap('kml');
    $this->mappr_map->get_request()->execute();
    ob_start();
    $this->mappr_map->create_output();
    $output = ob_get_contents();
    $file = ROOT."/public/tmp/map_kml.kml";
    file_put_contents($file, $output);
    ob_end_clean();
    session_destroy(); //req'd because Kml class sets a cookie
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/map_kml.kml'));
  }

  public function test_map_svg() {
    $this->setUpMap('svg');
    $this->mappr_map->get_request()->execute();
    ob_start();
    $this->mappr_map->create_output();
    $output = ob_get_contents();
    $file = ROOT."/public/tmp/map_svg.svg";
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/map_svg.svg'));
  }

}
