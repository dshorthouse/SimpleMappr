<?php

/**
 * Integration tests for settings on the Map Preview panel
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2015 David P. Shorthouse
 *
 */
class SettingsTest extends SimpleMapprTest
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
     * Test that selecting the State/Provinces layer makes a new image.
     */
    public function testLayerSelection()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->webDriver->findElement(WebDriverBy::id('stateprovinces'))->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }

    /**
     * Test that selecting the State/Provinces label makes a new image.
     */
    public function testLabelSelection()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->webDriver->findElement(WebDriverBy::id('stateprovnames'))->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }

    /**
     * Test that selecting graticules makes a new image.
     */
    public function testGraticules()
    {
        parent::setUpPage();

        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->webDriver->findElement(WebDriverBy::id('graticules'))->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }
}