<?php

/**
 * Unit tests for static methods and set-up of MapprWfs class
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
use SimpleMappr\Mappr\Wfs;

class WfsTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_wfs;

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

    private function makeWFS()
    {
        $mappr_wfs = new Wfs(['lakes', 'stateprovinces']);
        return $mappr_wfs;
    }

    /**
     * Test a GetCapabilities WFS response.
     */
    public function test_GetCapabilities()
    {
        $mappr_wfs = $this->makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $this->assertEquals('SimpleMappr Web Feature Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->FeatureTypeList->FeatureType));
    }

    /**
     * Test a GetFeature WFS response.
     */
    public function test_GetFeature1()
    {
        $req = [
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10'
        ];
        $this->setRequest($req);
        $mappr_wfs = $this->makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

    /**
     * Test a GetFeature WFS response with optional SRSNAME parameter
     */
    public function test_GetFeature2()
    {
        $req = [
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10',
            'SRSNAME' => 'EPSG:4326'
        ];
        $this->setRequest($req);
        $mappr_wfs = $this->makeWFS();
        $mappr_wfs->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($mappr_wfs));
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }
}
