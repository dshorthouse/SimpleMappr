<?php

/**
 * Unit tests for WFS class
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

class WfsBinaryTest extends TestCase
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
    public function test_wfs_getcapabilities()
    {
        $wfs = new Wfs(['lakes']);
        $wfs->makeService()->execute();
        ob_start();
        echo $wfs->createOutput();
        $output = ob_get_clean();
        $xml = simplexml_load_string($output);
        $layers = $xml->FeatureTypeList->FeatureType;
        $titles = [];
        foreach ($layers as $layer) {
            array_push($titles, $layer->Title);
        }
        $this->assertContains("lakes", $titles);
    }
}
