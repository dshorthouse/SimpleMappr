<?php

/**
 * Unit tests for static methods and set-up of MapprWms class
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\Wms;

class WmsTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_wms;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequestMethod();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequestMethod();
    }

    private function makeWMS()
    {
        $mappr_wms = new Wms(['lakes', 'stateprovinces']);
        return $mappr_wms;
    }

    /**
     * Test a GetCapabilities WMS response.
     */
    public function test_GetCapabilities()
    {
        $mappr_wms = $this->makeWMS();
        $mappr_wms->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wms));
        $this->assertEquals('SimpleMappr Web Map Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->Capability->Layer->Layer));
    }

    /**
     * Test a GetMap WMS response.
     */
    public function test_GetMap()
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
        $mappr_wms = $this->makeWMS();
        $mappr_wms->makeService()->execute();
        $image = imagecreatefromstring($this->ob_cleanOutput($mappr_wms));
        $this->assertEquals(imagesx($image), 400);
        $this->assertEquals(imagesy($image), 200);
    }

    /**
     * Test that case is ignored for requests.
     */
    public function test_CaseInsensitiveRequest()
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
        $mappr_wms = $this->makeWMS();
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
