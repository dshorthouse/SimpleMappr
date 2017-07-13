<?php

/**
 * Unit tests for Drawings tab
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */
class DrawingsTest extends SimpleMapprTestCase
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
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();

        $layer_id = 0;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');
        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsWKT']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '');
    }

    /**
     * Test clear a point data layer from a newly added layer.
     */
    public function testNewClearDrawingLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a drawing']"))->click();
        sleep(3);

        $layer_id = 3;
        $this->setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsWKT']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '');
    }
}
