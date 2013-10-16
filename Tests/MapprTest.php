<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml
 */

require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
 
class MapprTest extends WebTestCase {

  public function tearDown() {
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
  }

  public function test_response() {
    $this->get(WEB_SERVER . "/application");
    $this->assertResponse(200);
  }

  public function test_get_json_response() {
    $response = $this->get(WEB_SERVER . "/application");
    $this->assertMime("application/json");
  }

  public function test_post_json_response() {
    $response = $this->post(WEB_SERVER . "/application", array());
    $this->assertMime("application/json");
  }

  public function test_width() {
    $response = $this->post(WEB_SERVER . "/application", array('width' => '300'));
    $json = json_decode($response);
    $this->assertEqual($json->size[0], 300);
    $this->assertEqual($json->size[1], 150);
  }
}

?>