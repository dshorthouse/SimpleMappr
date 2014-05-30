<?php

/**
 * Integration tests for toolbar
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
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
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElement(WebDriverBy::className('toolsRefresh'));
        $link->click();
        parent::waitOnSpinner();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
        $this->assertNotEquals($default_img, $new_img);
    }

    /**
     * Test that rebuilding the map makes a new image at full extent.
     */
    public function testRebuild()
    {
        parent::setUpPage();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElement(WebDriverBy::className('toolsRebuild'));
        $link->click();
        parent::waitOnSpinner();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
        $this->assertNotEquals($default_img, $new_img);
    }

    /**
     * Test that zooming out from the toolbar makes a new image.
     */
    public function testZoomOut()
    {
        parent::setUpPage();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElement(WebDriverBy::className('toolsZoomOut'));
        $link->click();
        parent::waitOnSpinner();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
        $this->assertNotEquals($default_img, $new_img);
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

    /**
     * Test saving a map
     */
    public function testSaveMap()
    {
        $title = 'My New Map ' . time();
        parent::setUpPage();
        parent::setSession();
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::className('toolsSave'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::id('m-mapSaveTitle'))->sendKeys($title);
        $this->webDriver->findElement(WebDriverBy::xpath("//button/span[text()='Save']"))->click();
        parent::waitOnSpinner();
        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $saved_map_title = $this->webDriver->findElements(WebDriverBy::className('map-load'))[0];
        $this->assertEquals($title, $saved_map_title->getText());
    }

}