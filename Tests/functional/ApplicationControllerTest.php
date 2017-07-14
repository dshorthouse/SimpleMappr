<?php

/**
 * Unit tests for static methods and default set-up of MapprApplication class
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

class ApplicationControllerTest extends SimpleMapprFunctionalTestCase
{
    use SimpleMapprTestMixin;

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
     * Test that POST requests to /application.json are accepted.
     */
    public function test_postRequestJSON()
    {
        $response = $this->httpRequest(MAPPR_URL . "/application.json", [], "POST");
        $this->assertEquals("application/json; charset=UTF-8", $response["mime"]);
        $body = json_decode($response["body"], true);
        $this->assertContains(MAPPR_MAPS_URL, $body["mapOutputImage"]);
        $image = file_get_contents($body["mapOutputImage"]);
        $this->assertEquals("\x89PNG\x0d\x0a\x1a\x0a", substr($image, 0, 8));
    }

    /**
     * Test that POST requests to /application are accepted.
     */
    public function test_postRequestHTML()
    {
        $params = ["layers[countries]" => "on", "download" => true];
        $response = $this->httpRequest(MAPPR_URL . "/application", $params, "POST");
        $file = ROOT.'/public/tmp/apioutput_get.png';
        file_put_contents($file, $response["body"]);
        $this->assertEquals("image/png", $response["mime"]);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_get.png'));
    }

    /**
     * Test that js files are served from /public/javascript/.
     */
    public function test_jsAccessible()
    {
        $expected = "/*\n 2010-2017 David P. Shorthouse";
        $response = file_get_contents(MAPPR_URL . "/public/javascript/simplemappr.min.js");
        $this->assertEquals($expected, substr($response, 0, 33));
    }

    /**
     * Test that 404 is served when route is not found.
     */
    public function test_404()
    {
        $headers = get_headers(MAPPR_URL . "/doesnotexist");
        $this->assertEquals(404, substr($headers[0], 9, 3));
    }
}
