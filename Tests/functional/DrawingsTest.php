<?php

/**
 * Unit tests for Drawings tab
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class DrawingsTest extends SimpleMapprTest
{
    protected $title;
    protected $data;
    protected $color;

    private function setLayerContent($id)
    {
        $this->title = $this->webDriver->findElement(WebDriverBy::name('wkt['.$id.'][title]'));
        $this->data = $this->webDriver->findElement(WebDriverBy::name('wkt['.$id.'][data]'));
        $this->color = $this->webDriver->findElement(WebDriverBy::name('wkt['.$id.'][color]'));

        $this->title->sendKeys('My Layer');
        $this->data->sendKeys('POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->color->clear()->sendKeys('150 150 150');
    }

    /**
     * Test clear a point data layer.
     */
    public function testClearDrawingLayer()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();

        $layer_id = 0;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsWKT-panel-".$layer_id."']/button[text()='Clear']"))->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '');
    }

    /**
     * Test clear a point data layer from a newly added layer.
     */
    public function testNewClearDrawingLayer()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a drawing']"))->click();
        sleep(1);

        $layer_id = 3;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');

        $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='ui-accordion-fieldSetsWKT-panel-".$layer_id."']/button[text()='Clear']"))->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '');
    }
}