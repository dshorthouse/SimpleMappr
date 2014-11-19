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
class LayerDataTest extends SimpleMapprTest
{
    protected $title;
    protected $data;
    protected $color;

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

    private function setLayerContent($id)
    {
        $this->title = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][title]'));
        $this->data = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][data]'));
        $this->color = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][color]'));
        
        $this->title->sendKeys('My Layer');
        $this->data->sendKeys('Canada');
        $this->color->clear()->sendKeys('120 120 120');
    }

    /**
     * Test clear a point data layer.
     */
    public function testClearPointLayer()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Regions'));
        $link->click();

        $layer_id = 0;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'Canada');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsRegions-panel-".$layer_id."']/button[text()='Clear']"))->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '0 0 0');
    }

    /**
     * Test clear a point data layer from a newly added layer.
     */
    public function testNewClearPointLayer()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Regions'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a layer']"))->click();

        $layer_id = 3;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'Canada');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsRegions-panel-".$layer_id."']/button[text()='Clear']"))->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '0 0 0');
    }
}