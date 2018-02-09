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
use SimpleMappr\Mappr\WebServices\Wfs;

/**
 * Test Wfs class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class WfsTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_wfs;

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
     * Create the WFS resoponse object
     *
     * @return object
     */
    private function _makeWFS()
    {
        $mappr_wfs = new Wfs(['lakes', 'stateprovinces']);
        return $mappr_wfs;
    }

    /**
     * Test a GetCapabilities WFS response.
     *
     * @return void
     */
    public function testGetCapabilities()
    {
        $mappr_wfs = $this->_makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $this->assertEquals('SimpleMappr Web Feature Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->FeatureTypeList->FeatureType));
    }

    /**
     * Test a GetFeature WFS response.
     *
     * @return void
     */
    public function testGetFeature1()
    {
        $req = [
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10'
        ];
        $this->setRequest($req);
        $mappr_wfs = $this->_makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

    /**
     * Test a GetFeature WFS response with optional SRSNAME parameter
     *
     * @return void
     */
    public function testGetFeature2()
    {
        $req = [
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10',
            'SRSNAME' => 'EPSG:4326'
        ];
        $this->setRequest($req);
        $mappr_wfs = $this->_makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }
}
