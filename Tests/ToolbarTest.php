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
  
  public function testMissingTitle() {
    parent::setUpPage();
    $this->webDriver->findElement(WebDriverBy::linkText('Point Data'))->click();
    $coord_box = $this->webDriver->findElements(WebDriverBy::className('m-mapCoord'))[0];
    $coord_box->sendKeys("45, -120");
    $button = $this->webDriver->findElements(WebDriverBy::className('submitForm'))[0];
    $button->click();
    $message_box = $this->webDriver->findElement(WebDriverBy::id('mapper-message'));
    $this->assertTrue($message_box->isDisplayed());
    $this->assertEquals("You are missing a legend for at least one of your Point Data or Regions layers.", $message_box->getText());
  }
}

?>