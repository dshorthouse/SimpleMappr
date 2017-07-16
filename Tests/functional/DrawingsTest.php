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

/**
 * Test Drawing WKT for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class DrawingsTest extends SimpleMapprFunctionalTestCase
{
    protected $title;
    protected $data;
    protected $color;

    /**
     * Set the form content based on integer
     *
     * @param integer $id The identifier
     *
     * @return void
     */
    private function _setLayerContent($id)
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
     *
     * @return void
     */
    public function testClearDrawingLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();

        $layer_id = 0;
        $this->_setLayerContent($layer_id);

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
     *
     * @return void
     */
    public function testNewClearDrawingLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Drawings'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a drawing']"))->click();
        sleep(3);

        $layer_id = 3;
        $this->_setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsWKT']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '');
    }
}
