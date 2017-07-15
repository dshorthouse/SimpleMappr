<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

class PointDataTest extends SimpleMapprFunctionalTestCase
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
