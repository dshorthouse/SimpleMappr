<?php

/**
 * Unit tests for static methods and set-up of Kml class
 */

class KmlTest extends PHPUnit_Framework_TestCase {

  protected $kml;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->kml = new \SimpleMappr\Kml();
  }

  protected function tearDown() {
    session_destroy(); //req'd because Kml class sets a cookie
    unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST']);
    $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml,json,svg}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  public function test_kml() {
    $coords = array(
      array(
        'title' => 'Sample Data',
        'data' => "55, -115\n65, -110",
        'shape' => 'star',
        'size' => 14,
        'color' => '255 32 3'
      ),
      array(
        'title' => 'Sample Data2',
        'data' => "35, -120\n70, -80",
        'shape' => 'circle',
        'size' => 14,
        'color' => '255 32 3'
      )
    );
    $this->kml->get_request("My Map", $coords);
    ob_start();
    $this->kml->create_output();
    $output = ob_get_contents();
    $file = ROOT."/public/tmp/kml.kml";
    file_put_contents($file, $output);
    ob_end_clean();
    $this->assertTrue(SimpleMapprTest::files_identical($file, ROOT.'/Tests/files/kml.kml'));
  }

}
