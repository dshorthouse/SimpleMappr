<?php

/**
 * Unit tests for static methods and set-up of MapprWfs class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
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
        ob_start();
        echo $mappr_wfs->createOutput();
        $xml = simplexml_load_string(ob_get_clean());
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
        ob_start();
        echo $mappr_wfs->createOutput();
        $xml = simplexml_load_string(ob_get_clean());
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
        ob_start();
        echo $mappr_wfs->createOutput();
        $xml = simplexml_load_string(ob_get_clean());
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

}