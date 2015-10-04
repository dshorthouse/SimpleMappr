<?php

/**
 * Unit tests for static methods and default set-up of MapprApplication class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2015 David P. Shorthouse
 *
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearTmpFiles();
    }

    /**
     * Test that POST requests are accepted.
     */
    public function test_postRequest()
    {
        $fields = array();
        $response = $this->httpPost("http://" . MAPPR_DOMAIN . "/application.json", $fields);
        $body = json_decode($response, true);
        $this->assertContains(MAPPR_MAPS_URL, $body["mapOutputImage"]);
        $image = file_get_contents($body["mapOutputImage"]);
        $this->assertEquals("\x89PNG\x0d\x0a\x1a\x0a",substr($image,0,8));
    }

}