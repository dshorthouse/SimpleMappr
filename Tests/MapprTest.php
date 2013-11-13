<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class MapprTest extends PHPUnit_Extensions_Selenium2TestCase {

  protected $app_url;

  protected function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/";
    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->app_url);
  }

  protected function tearDown() {
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
    Header::flush_cache(false);
  }

  public function setUpPage() {
    $this->url("/");
    $this->waitOnSpinner();
  }

  public function waitOnSpinner() {
    while ($this->byId('map-loader')->displayed()) {
      sleep(1);
    }
  }

  public function testRefresh() {
    $class = "toolsRefresh";
    $link = $this->byClassName($class);
    $link->click();
    $this->waitOnSpinner();
    $img_url = $this->byId('mapOutputImage')->attribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $img_url);
  }
}

?>