<?php

/**
 * Integration tests for toolbar
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class ToolbarTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testRefresh() {
    parent::setUpPage();
    $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsRefresh'));
    $link->click();
    parent::waitOnSpinner();
    $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $new_img);
    $this->assertNotEquals($default_img, $new_img);
  }

  public function testRebuild() {
    parent::setUpPage();
    $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsRebuild'));
    $link->click();
    parent::waitOnSpinner();
    $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $new_img);
    $this->assertNotEquals($default_img, $new_img);
  }

/*
  public function testDownloadDialog() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsDownload'));
    $link->click();
    $this->assertTrue($this->webDriver->findElement(WebDriverBy::id('mapExport'))->isDisplayed());
  }
*/

  public function testFill() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsQuery'));
    $link->click();
    $color_picker = WebDriverBy::className('colorpicker');
    $this->webDriver->wait(10)->until(WebDriverExpectedCondition::visibilityOfElementLocated($color_picker));
    $this->webDriver->findElement(WebDriverBy::cssSelector())
  }

  public function testZoomOut() {
    parent::setUpPage();
    $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $link = $this->webDriver->findElement(WebDriverBy::className('toolsZoomOut'));
    $link->click();
    parent::waitOnSpinner();
    $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
    $this->assertContains(MAPPR_MAPS_URL, $new_img);
    $this->assertNotEquals($default_img, $new_img);
  }
}

?>