<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

require_once("SimpleMapprTest.php");

class MapprTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function setUpPage() {
    new Header;
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