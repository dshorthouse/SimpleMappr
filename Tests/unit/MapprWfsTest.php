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
class MapprWfsTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $mappr_wfs;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequest();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
    }

    private function makeWFS()
    {
        $mappr_wfs = new \SimpleMappr\MapprWfs();
        $mappr_wfs->wfs_layers = array(
            'lakes' => 'on',
            'stateprovinces_polygon' => 'on'
        );
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
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $this->assertEquals('SimpleMappr Web Feature Service', $xml->Service->Title);
    }

    /**
     * Test a GetFeature WFS response.
     */
    public function test_GetFeature1()
    {
        $_REQUEST = array(
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10'
        );
        $mappr_wfs = $this->makeWFS();
        $mappr_wfs->makeService()->execute();
        ob_start();
        echo $mappr_wfs->createOutput();
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

    /**
     * Test a GetFeature WFS response with optional SRSNAME parameter
     */
    public function test_GetFeature2()
    {
        $_REQUEST = array(
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10',
            'SRSNAME' => 'EPSG:4326'
        );
        $mappr_wfs = $this->makeWFS();
        $mappr_wfs->makeService()->execute();
        ob_start();
        echo $mappr_wfs->createOutput();
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

}