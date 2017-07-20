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

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\WebServices\Wms;

/**
 * Test Wms class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class WmsTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_wms;

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
     * Make the Wms object
     *
     * @return object
     */
    private function _makeWMS()
    {
        $mappr_wms = new Wms(['lakes', 'stateprovinces']);
        return $mappr_wms;
    }

    /**
     * Test a GetCapabilities WMS response.
     *
     * @return void
     */
    public function testGetCapabilities()
    {
        $mappr_wms = $this->_makeWMS();
        $mappr_wms->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wms));
        $this->assertEquals('SimpleMappr Web Map Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->Capability->Layer->Layer));
    }

    /**
     * Test a GetMap WMS response.
     *
     * @return void
     */
    public function testGetMap()
    {
        $req = [
            'REQUEST' => 'GetMap',
            'LAYERS' => 'lakes',
            'BBOX' => '-120,45,-70,70',
            'SRS' => 'epsg:4326',
            'WIDTH' => 400,
            'HEIGHT' => 200
        ];
        $this->setRequest($req);
        $mappr_wms = $this->_makeWMS();
        $mappr_wms->makeService()->execute();
        $image = imagecreatefromstring($this->ob_cleanOutput($mappr_wms));
        $this->assertEquals(imagesx($image), 400);
        $this->assertEquals(imagesy($image), 200);
    }

    /**
     * Test that case is ignored for requests.
     *
     * @return void
     */
    public function testCaseInsensitiveRequest()
    {
        $req = [
          'request' => 'GetMap',
          'layers' => 'lakes',
          'bbox' => '-120,45,-70,70',
          'srs' => 'epsg:4326',
          'width' => 400,
          'height' => 200
        ];
        $this->setRequest($req);
        $mappr_wms = $this->_makeWMS();
        $this->assertEquals($mappr_wms->request->params['REQUEST'], $_REQUEST['request']);
        $this->assertEquals($mappr_wms->request->params['LAYERS'], $_REQUEST['layers']);
        $this->assertEquals($mappr_wms->request->params['BBOX'], $_REQUEST['bbox']);
        $this->assertEquals($mappr_wms->request->params['SRS'], $_REQUEST['srs']);
        $this->assertEquals($mappr_wms->request->params['WIDTH'], $_REQUEST['width']);
        $this->assertEquals($mappr_wms->request->params['HEIGHT'], $_REQUEST['height']);
        $this->assertEquals($mappr_wms->request->params['VERSION'], '1.1.1');
        $this->assertEquals($mappr_wms->request->params['FORMAT'], 'image/png');
    }
}
