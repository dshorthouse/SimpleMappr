<?php

/**
 * Unit tests for WMS class
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

class WmsBinaryTest extends TestCase
{
    use SimpleMapprTestMixin;

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
        $wms = new Wms(['lakes']);
        $wms->makeService()->execute();
        $xml = simplexml_load_string($this->ob_cleanOutput($wms));
        $layers = $xml->Capability->Layer->Layer;
        $titles = [];
        foreach ($layers as $layer) {
            array_push($titles, $layer->Title);
        }
        $this->assertContains("lakes", $titles);
    }
}
