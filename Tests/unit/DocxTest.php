<?php

/**
 * Unit tests for static methods and set-up of MapprApi class
 */

class DocxTest extends PHPUnit_Framework_TestCase {

  protected $mappr_api;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->mappr_docx = new \SimpleMappr\MapprDocx();
    $this->mappr_docx->set_shape_path(ROOT."/lib/mapserver/maps")
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

  public function test_docx_mime() {
    $mappr_api = $this->mappr_docx->get_request()->execute();
    ob_start();
    $this->mappr_docx->create_output();
    $output = ob_get_contents();
    ob_end_clean();
    $finfo = new finfo(FILEINFO_MIME);
    $mime = $finfo->buffer($output);
    $this->assertEquals("application/zip; charset=binary", $mime);
  }

}
