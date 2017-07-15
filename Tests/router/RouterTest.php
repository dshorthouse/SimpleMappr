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
use \SimpleMappr\Utility;

/**
 * Test the Router Class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class RouterTest extends SimpleMapprTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test GET /
     *
     * @return void
     */
    public function testMainGET()
    {
        $response = $this->httpRequest(MAPPR_URL);
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
    }

    /**
     * Test GET /about
     *
     * @return void
     */
    public function testAboutGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/about");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
    }

    /**
     * Test GET /api
     *
     * @return void
     */
    public function testApiGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/api");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('image/png', $response['mime']);
    }

    /**
     * Test POST /api
     *
     * @return void
     */
    public function testApiPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/api", [], "POST");
        $this->assertEquals(303, $response['code']);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
    }

    /**
     * Test GET /apidoc
     *
     * @return void
     */
    public function testApidocGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/apidoc");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /appplication
     *
     * @return void
     */
    public function testApplicationPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/application", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /appplication with params
     *
     * @return void
     */
    public function testApplicationPOST2()
    {
        $params = [
            "download" => true,
            "output" => "png"
        ];
        $response = $this->httpRequest(MAPPR_URL . "/application", $params, "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('image/png', $response['mime']);
    }
    
    /**
     * Test POST /appplication.json
     *
     * @return void
     */
    public function testApplicationJsonPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/application.json", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /citation
     *
     * @return void
     */
    public function testCitationPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/citation", [], "POST");
        $this->assertEquals(403, $response['code']);
    }

    /**
     * Test GET /citation.json
     *
     * @return void
     */
    public function testCitationJsonGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/citation.json");
        $this->assertEquals(403, $response['code']);
    }

    /**
     * Test GET /citation.rss
     *
     * @return void
     */
    public function testCitationRssGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/citation.rss");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/xml', $response['mime']);
    }

    /**
     * Test POST /docx
     *
     * @return void
     */
    public function testDocxPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/docx", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $response['mime']);
    }

    /**
     * Test GET /feedback
     *
     * @return void
     */
    public function testFeedbackGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/feedback");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
    }

    /**
     * Test GET /help
     *
     * @return void
     */
    public function testHelpGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/help");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /kml
     *
     * @return void
     */
    public function testKmlPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/kml", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/vnd.google-earth.kml+xml kml; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /pptx
     *
     * @return void
     */
    public function testPptxGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/pptx", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.presentationml.presentation', $response['mime']);
    }

    /**
     * Test POST /query
     *
     * @return void
     */
    public function testQueryGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/query", [], "POST");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
    }

    /**
     * Test GET /swagger.json
     *
     * @return void
     */
    public function testSwaggerGET()
    {
        $response = $this->httpRequest(MAPPR_URL . "/swagger.json");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
    }

    /**
     * Test POST /usermap
     *
     * @return void
     */
    public function testUsermapPOST()
    {
        $response = $this->httpRequest(MAPPR_URL . "/usermap", [], "POST");
        $this->assertEquals(403, $response['code']);
    }

    /**
     * Test DELETE /usermap
     *
     * @return void
     */
    public function testUsermapDELETE()
    {
        $response = $this->httpRequest(MAPPR_URL . "/usermap/1", [], "DELETE");
        $this->assertEquals(403, $response['code']);
    }

    /**
     * Test GET /wfs
     *
     * @return void
     */
    public function testWfsGET()
    {
        $params = [
            "SERVICE" => "WFS",
            "REQUEST" => "DescribeFeatureType",
            "VERSION" => "1.0.0",
            "TYPENAME" => "base"
        ];
        $response = $this->httpRequest(MAPPR_URL . "/wfs", $params, "GET");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('application/xml', $response['mime']);
    }

    /**
     * Test GET /wms
     *
     * @return void
     */
    public function testWmsGET()
    {
        $params = [
            "SERVICE" => "WMS",
            "REQUEST" => "GetMap",
            "LAYERS" => "base"
        ];
        $response = $this->httpRequest(MAPPR_URL . "/wms", $params, "GET");
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('image/png', $response['mime']);
    }
}
