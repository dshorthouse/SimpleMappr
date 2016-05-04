<?php

/**
 * Integration tests for toolbar
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class ToolbarTest extends SimpleMapprTest
{
    /**
     * Parent setUp function executed before each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test that refreshing the map makes a new image.
     */
    public function testRefresh()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsRefresh'))[0];
        $link->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }

    /**
     * Test that rebuilding the map makes a new image at full extent.
     */
    public function testRebuild()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsRebuild'))[0];
        $link->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }

    /**
     * Test that zooming out from the toolbar makes a new image.
     */
    public function testZoomOut()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsZoomOut'))[0];
        $link->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }

    /**
     * Test that a message is shown to user when a title is missing for a layer.
     */
    public function testMissingTitle()
    {
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