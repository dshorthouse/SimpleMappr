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
 * Test Regions layer for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class RegionsTest extends SimpleMapprFunctionalTestCase
{
    protected $title;
    protected $data;
    protected $color;
    protected $hatch;

    /**
     * Set the form content based on integer of elements
     *
     * @param integer $id Identifier of form element
     *
     * @return void
     */
    private function _setLayerContent($id)
    {
        $this->title = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][title]'));
        $this->data = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][data]'));
        $this->color = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][color]'));
        $this->hatch = $this->webDriver->findElement(WebDriverBy::name('regions['.$id.'][hatch]'));
        
        $this->title->sendKeys('My Layer');
        $this->data->sendKeys('Canada');
        $this->color->clear()->sendKeys('120 120 120');
        $this->hatch->click();
    }

    /**
     * Test clear a point data layer.
     *
     * @return void
     */
    public function testClearRegionLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Regions'));
        $link->click();

        $layer_id = 0;
        $this->_setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'Canada');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');
        $this->assertTrue($this->hatch->isSelected());

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsRegions']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');
        $this->assertFalse($this->hatch->isSelected());
    }

    /**
     * Test clear a point data layer from a newly added layer.
     *
     * @return void
     */
    public function testNewClearRegionLayer()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Regions'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add a region']"))->click();
        sleep(1);

        $layer_id = 3;
        $this->_setLayerContent($layer_id);

        $this->assertEquals($this->title->getAttribute('value'), 'My Layer');
        $this->assertEquals($this->data->getAttribute('value'), 'Canada');
        $this->assertEquals($this->color->getAttribute('value'), '120 120 120');
        $this->assertTrue($this->hatch->isSelected());

        $this->webDriver->findElements(WebDriverBy::xpath("//div[@id='fieldSetsRegions']//button[text()='Clear']"))[$layer_id]->click();

        $this->assertEquals($this->title->getAttribute('value'), '');
        $this->assertEquals($this->data->getAttribute('value'), '');
        $this->assertEquals($this->color->getAttribute('value'), '150 150 150');
        $this->assertFalse($this->hatch->isSelected());
    }
}
