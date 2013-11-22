<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class MapprTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testRefresh() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsRefresh'));
    $link->click();
    parent::waitOnSpinner();
    $img_url = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $img_url);
  }

}

?>