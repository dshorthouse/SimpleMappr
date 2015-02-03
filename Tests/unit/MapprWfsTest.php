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
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->mappr_wfs = new \SimpleMappr\MapprWfs();
        $this->mappr_wfs->wfs_layers = array(
            'lakes' => 'on',
            'stateprovinces_polygon' => 'on'
        );
        $this->mappr_wfs = $this->setMapprDefaults($this->mappr_wfs);
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
    }

    /**
     * Test a GetCapabilities WFS response.
     */
    public function test_GetCapabilities()
    {
        $mappr_wfs = $this->mappr_wfs->get_request()->make_service()->execute();
        ob_start();
        echo $mappr_wfs->create_output();
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $this->assertEquals('SimpleMappr Web Feature Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->FeatureTypeList->FeatureType));
    }

    /**
     * Test a GetFeature WFS response.
     */
    public function test_GetFeature()
    {
        $_REQUEST = array(
            'REQUEST' => 'GetFeature',
            'TYPENAME' => 'lakes',
            'MAXFEATURES' => '10'
        );
        $mappr_wfs = $this->mappr_wfs->get_request()->make_service()->execute();
        ob_start();
        echo $mappr_wfs->create_output();
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $ns = $xml->getNamespaces(true);
        $this->assertEquals(10, count($xml->children($ns['gml'])->featureMember));
    }

}