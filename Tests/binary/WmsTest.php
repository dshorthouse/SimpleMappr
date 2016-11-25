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

use PHPUnit\Framework\TestCase;
use SimpleMappr\MapprWms;

class WmsTest extends TestCase
{
    use SimpleMapprMixin;

    protected $wms;

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
        $this->clearTmpFiles();
    }

    /**
     * Test that GetCapabilities request is handled.
     */
    public function test_wms_getcapabilities()
    {
        $wms = new MapprWms(['lakes']);
        $wms->makeService()->execute();
        ob_start();
        echo $wms->createOutput();
        $output = ob_get_contents();
        $xml = simplexml_load_string($output);
        ob_end_clean();
        $layers = $xml->Capability->Layer->Layer;
        $titles = [];
        foreach($layers as $layer) {
            array_push($titles, $layer->Title);
        }
        $this->assertContains("lakes", $titles);
    }

}