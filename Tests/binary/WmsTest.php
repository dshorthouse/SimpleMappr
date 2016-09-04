<?php

/**
 * Unit tests for WMS class
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class WmsTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $wms;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
        $this->clearTmpFiles();
    }

    /**
     * Test that GetCapabilities request is handled.
     */
    public function test_wms_getcapabilities()
    {
        $_REQUEST = array();
        $wms = new \SimpleMappr\MapprWms();
        $wms->wms_layers = array('lakes' => 'on');
        $wms->makeService()->execute();
        ob_start();
        echo $wms->createOutput();
        $output = ob_get_contents();
        $xml = simplexml_load_string($output);
        ob_end_clean();
        $layers = $xml->Capability->Layer->Layer;
        $titles = array();
        foreach($layers as $layer) {
            array_push($titles, $layer->Title);
        }
        $this->assertContains("lakes", $titles);
    }

}