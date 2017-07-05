<?php

/**
 * Unit tests for Point Data tab
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class PointDataTest extends SimpleMapprTestCase
{
    protected $title;
    protected $data;
    protected $shape;
    protected $size;
    protected $color;

    private function setLayerContent($id)
    {
        $this->title = $this->webDriver->findElement(WebDriverBy::name('coords['.$id.'][title]'));
        $this->data = $this->webDriver->findElement(WebDriverBy::name('coords['.$id.'][data]'));
        $this->shape = $this->webDriver->findElement(WebDriverBy::name('coords['.$id.'][shape]'));
        $this->size = $this->webDriver->findElement(WebDriverBy::name('coords['.$id.'][size]'));
        $this->color = $this->webDriver->findElement(WebDriverBy::name('coords['.$id.'][color]'));

        $this->title->sendKeys('My Layer');
        $this->data->sendKeys('45, -120');
        $selected_shape = new WebDriverSelect($this->shape);
        $selected_shape->selectByValue('plus');
        $selected_size = new WebDriverSelect($this->size);
        $selected_size->selectByValue('16');
        $this->color->clear()->sendKeys('120 120 120');
    }

    /**
     * Test clear a point data layer.
     */
    public function testClearPointLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $layer_id = 0;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), '45, -120');
        $this->assertEquals($this->shape->getAttribute('value'), 'plus');
        $this->assertEquals($this->size->getAttribute('value'), '16');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsPoints']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->shape->getAttribute('value'), 'circle');
        $this->assertEquals($this->size->getAttribute('value'), '10');
        $this->assertEquals($this->color->getAttribute('value'), '0 0 0');
    }

    /**
     * Test clear a point data layer from a newly added layer.
     */
    public function testNewClearPointLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a layer']"))->click();
        sleep(2);

        $layer_id = 3;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), '45, -120');
        $this->assertEquals($this->shape->getAttribute('value'), 'plus');
        $this->assertEquals($this->size->getAttribute('value'), '16');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsPoints']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->shape->getAttribute('value'), 'circle');
        $this->assertEquals($this->size->getAttribute('value'), '10');
        $this->assertEquals($this->color->getAttribute('value'), '0 0 0');
    }
}
