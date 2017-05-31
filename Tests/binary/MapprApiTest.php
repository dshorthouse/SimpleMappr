<?php

/**
 * Unit tests for static methods and set-up of MapprApi class
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\MapprApi;

class MapprApiTest extends TestCase
{
    use SimpleMapprMixin;

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
     * Test that a ping request is produced.
     */
    public function test_api_ping()
    {
        $this->setRequest(['ping' => true]);
        $mappr_api = new MapprApi;
        $output = $mappr_api->execute()->createOutput();
        $decoded = json_decode($output, true);
        $this->assertArrayHasKey("status", $decoded);
    }

    /**
     * Test that a simple POST request is handled.
     */
    public function test_apioutput_post()
    {
        $this->setRequestMethod('POST');
        $mappr_api = new MapprApi;
        $output = $mappr_api->execute()->createOutput();
        $decoded = json_decode($output, true);
        $this->assertArrayHasKey("imageURL", $decoded);
        $this->assertArrayHasKey("expiry", $decoded);
        $this->assertContains(MAPPR_MAPS_URL, $decoded["imageURL"]);
    }

    /**
     * Test that a simple GET request is handled.
     */
    public function test_apioutput_get()
    {
        $this->setRequest([]);
        $mappr_api = new MapprApi;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_get.png';
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_get.png'));
    }
}