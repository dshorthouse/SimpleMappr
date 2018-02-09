<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\Application\Query;

/**
 * Test Query class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class QueryTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_query;

    /**
     * Parent setUp function executed before each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setRequestMethod();
    }

    /**
     * Parent tearDown function executed after each test.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->clearRequestMethod();
    }

    /**
     * Test return of country name with query.
     *
     * @return void
     */
    public function testCountry()
    {
        $req = [
          'bbox_query' => '176,83,176,83'
        ];
        $this->setRequest($req);
        $mappr_query = new Query;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertEquals('Canada', $output[0]);
    }

    /**
     * Test that many country names are returned with a large extent.
     *
     * @return void
     */
    public function testManyCountries()
    {
        $req = [
            'bbox_query' => '786,272,900,358'
        ];
        $this->setRequest($req);
        $mappr_query = new Query;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertTrue(in_array("Australia", $output));
        $this->assertTrue(in_array("New Zealand", $output));
    }

    /**
     * Test that a StateProvince code in returned when qlayer is provided.
     *
     * @return void
     */
    public function testStateProvince()
    {
        $req = [
            'bbox_query' => '176,83,176,83',
            'qlayer' => 'stateprovinces_polygon'
        ];
        $this->setRequest($req);
        $mappr_query = new Query;
        $mappr_query->execute()->queryLayer();
        $output = $mappr_query->data;
        $this->assertEquals('CAN[SK]', $output[0]);
    }
}
