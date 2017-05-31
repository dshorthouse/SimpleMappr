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
        $mappr_api->execute();
        $output = $mappr_api->createOutput();
        $decoded = json_decode($output, true);
        $this->assertArrayHasKey("status", $decoded);
    }
}