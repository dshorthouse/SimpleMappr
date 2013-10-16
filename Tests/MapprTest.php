<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: 
 */

require_once('DatabaseTest.php');

class MapprTest extends DatabaseTest {

  private static $mappr;

  public static function setUpBeforeClass() {
     $root = dirname(dirname(__FILE__));
     $mappr = new Mappr();
     $mappr->set_shape_path($root."/lib/mapserver/maps")
          ->set_font_file($root."/lib/mapserver/fonts/fonts.list")
          ->set_tmp_path($root."/public/tmp/")
          ->set_tmp_url(MAPPR_MAPS_URL)
          ->set_default_projection("epsg:4326")
          ->set_max_extent("-180,-90,180,90")
  }

  public static function tearDownAfterClass() {
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

//
//  public function test_refresh() {
//  }
}

?>