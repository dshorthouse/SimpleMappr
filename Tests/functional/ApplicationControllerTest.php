<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
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
