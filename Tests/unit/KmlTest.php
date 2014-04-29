<?php

/**
 * Unit tests for static methods and set-up of Kml class
 */
class KmlTest extends PHPUnit_Framework_TestCase {

  protected $kml;

  protected function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->kml = new Kml();
  }

  protected function tearDown() {
    session_destroy();
  }

  public function test_kml_mime() {
    $this->kml->get_request();
    ob_start();
    $this->kml->create_output();
    $output = ob_get_contents();
    ob_end_clean();
    $finfo = new finfo(FILEINFO_MIME);
    $mime = $finfo->buffer($output);
    $this->assertEquals("application/xml; charset=us-ascii", $mime);
  }

}
