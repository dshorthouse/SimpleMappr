<?php

/**
 * Unit tests for static methods and set-up of MapprWms class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class MapprWmsTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $mappr_wms;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequest();
        $this->mappr_wms = new \SimpleMappr\MapprWms();
        $this->mappr_wms->wms_layers = array(
            'lakes' => 'on',
            'stateprovinces_polygon' => 'on'
        );
        $this->mappr_wms = $this->setMapprDefaults($this->mappr_wms);
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
    }

    /**
     * Test a GetCapabilities WMS response.
     */
    public function test_GetCapabilities()
    {
        $mappr_wms = $this->mappr_wms->get_request()->makeService()->execute();
        ob_start();
        $mappr_wms->createOutput();
        $xml = simplexml_load_string(ob_get_contents());
        ob_end_clean();
        $this->assertEquals('SimpleMappr Web Map Service', $xml->Service->Title);
        $this->assertEquals(3, count($xml->Capability->Layer->Layer));
    }

    /**
     * Test a GetMap WMS response.
     */
    public function test_GetMap()
    {
        $_REQUEST = array(
            'REQUEST' => 'GetMap',
            'LAYERS' => 'lakes',
            'BBOX' => '-120,45,-70,70',
            'SRS' => 'epsg:4326',
            'WIDTH' => 400,
            'HEIGHT' => 200
        );
        $mappr_wms = $this->mappr_wms->get_request()->makeService()->execute();
        ob_start();
        $mappr_wms->createOutput();
        $image = imagecreatefromstring(ob_get_contents());
        ob_end_clean();
        $this->assertEquals(imagesx($image), 400);
        $this->assertEquals(imagesy($image), 200);
    }

    /**
     * Test that case is ignored for requests.
     */
    public function test_CaseInsensitiveRequest()
    {
        $_REQUEST = array(
          'request' => 'GetMap',
          'layers' => 'lakes',
          'bbox' => '-120,45,-70,70',
          'srs' => 'epsg:4326',
          'width' => 400,
          'height' => 200
        );
        $mappr_wms = $this->mappr_wms->get_request();
        $this->assertEquals($this->mappr_wms->params['REQUEST'], $_REQUEST['request']);
        $this->assertEquals($this->mappr_wms->params['LAYERS'], $_REQUEST['layers']);
        $this->assertEquals($this->mappr_wms->params['BBOX'], $_REQUEST['bbox']);
        $this->assertEquals($this->mappr_wms->params['SRS'], $_REQUEST['srs']);
        $this->assertEquals($this->mappr_wms->params['WIDTH'], $_REQUEST['width']);
        $this->assertEquals($this->mappr_wms->params['HEIGHT'], $_REQUEST['height']);
        $this->assertEquals($this->mappr_wms->params['VERSION'], '1.1.1');
        $this->assertEquals($this->mappr_wms->params['FORMAT'], 'image/png');
    }

}