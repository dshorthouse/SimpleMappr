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
  }

  public function setUpPage() {
    $this->url("/");
  }

  public function spinner() {
    return $this->byId('map-loader')->displayed();
  }

  public function testRefresh() {
    $class = "toolsRefresh";
    $link = $this->byClassName($class);
    $link->click();
    while ($this->spinner()) {
      sleep(1);
    }
    $img_url = $this->byId('mapOutputImage')->attribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $img_url);
  }
}

?>