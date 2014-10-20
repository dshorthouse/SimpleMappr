<?php

/**
 * Unit tests for Point Data tab
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class PointDataTest extends SimpleMapprTest
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
     * Test clear a point data layer.
     */
    public function testClearPointLayer()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $title = $this->webDriver->findElement(WebDriverBy::name('coords[0][title]'));
        $points = $this->webDriver->findElement(WebDriverBy::name('coords[0][data]'));
        $shape = $this->webDriver->findElement(WebDriverBy::name('coords[0][shape]'));
        $size = $this->webDriver->findElement(WebDriverBy::name('coords[0][size]'));
        $color = $this->webDriver->findElement(WebDriverBy::name('coords[0][color]'));

        $title->sendKeys('My Layer');
        $this->assertEquals($title->getAttribute('value'), 'My Layer');

        $points->sendKeys('45, -120');
        $this->assertEquals($points->getAttribute('value'), '45, -120');

        $selected_shape = new WebDriverSelect($shape);
        $selected_shape->selectByValue('plus');
        $this->assertEquals($shape->getAttribute('value'), 'plus');

        $selected_size = new WebDriverSelect($size);
        $selected_size->selectByValue('16');
        $this->assertEquals($size->getAttribute('value'), '16');

        $color->clear()->sendKeys('120 120 120');
        $this->assertEquals($color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsPoints-panel-0']/button[text()='Clear']"))->click();

        $this->assertEquals($title->getAttribute('value'), '');
        $this->assertEquals($points->getAttribute('value'), '');
        $this->assertEquals($shape->getAttribute('value'), 'circle');
        $this->assertEquals($size->getAttribute('value'), '10');
        $this->assertEquals($color->getAttribute('value'), '0 0 0');
    }

    /**
     * Test clear a point data layer from a newly added layer.
     */
    public function testNewClearPointLayer()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a layer']"))->click();

        $title = $this->webDriver->findElement(WebDriverBy::name('coords[3][title]'));
        $points = $this->webDriver->findElement(WebDriverBy::name('coords[3][data]'));
        $shape = $this->webDriver->findElement(WebDriverBy::name('coords[3][shape]'));
        $size = $this->webDriver->findElement(WebDriverBy::name('coords[3][size]'));
        $color = $this->webDriver->findElement(WebDriverBy::name('coords[3][color]'));

        $title->sendKeys('My Layer');
        $this->assertEquals($title->getAttribute('value'), 'My Layer');

        $points->sendKeys('45, -120');
        $this->assertEquals($points->getAttribute('value'), '45, -120');

        $selected_shape = new WebDriverSelect($shape);
        $selected_shape->selectByValue('plus');
        $this->assertEquals($shape->getAttribute('value'), 'plus');

        $selected_size = new WebDriverSelect($size);
        $selected_size->selectByValue('16');
        $this->assertEquals($size->getAttribute('value'), '16');

        $color->clear()->sendKeys('120 120 120');
        $this->assertEquals($color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsPoints-panel-3']/button[text()='Clear']"))->click();

        $this->assertEquals($title->getAttribute('value'), '');
        $this->assertEquals($points->getAttribute('value'), '');
        $this->assertEquals($shape->getAttribute('value'), 'circle');
        $this->assertEquals($size->getAttribute('value'), '10');
        $this->assertEquals($color->getAttribute('value'), '0 0 0');
    }
}