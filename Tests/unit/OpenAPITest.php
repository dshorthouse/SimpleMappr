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
use SimpleMappr\Controller\OpenApi;

/**
 * Test OpenAPI class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class OpenApiTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $swagger;

    /**
     * Parent setUp function executed before each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $open_api = new OpenApi;
        $this->swagger = $open_api->index();
        $this->parameters = $this->swagger["paths"]["/api"]["get"]["parameters"];
    }

    /**
     * Test that swagger key is produced in OpenAPI.
     *
     * @return void
     */
    public function testSwagger()
    {
        $this->assertArrayHasKey("swagger", $this->swagger);
        $this->assertEquals("2.0", $this->swagger["swagger"]);
    }

    /**
     * Test that info key is produced in OpenAPI.
     *
     * @return void
     */
    public function testInfo()
    {
        $this->assertArrayHasKey("info", $this->swagger);
        $this->assertEquals("SimpleMappr API", $this->swagger["info"]["title"]);
    }

    /**
     * Test that host key is produced in OpenAPI.
     *
     * @return void
     */
    public function testHost()
    {
        $this->assertArrayHasKey("host", $this->swagger);
    }

    /**
     * Test that schemes key is produced in OpenAPI.
     *
     * @return void
     */
    public function testSchemes()
    {
        $this->assertArrayHasKey("schemes", $this->swagger);
        $this->assertEquals(["http"], $this->swagger["schemes"]);
    }

    /**
     * Test number of parameters
     *
     * @return void
     */
    public function testNumberParameters()
    {
        $this->assertCount(28, $this->parameters);
    }

    /**
     * Test shape[x] enum contains correct count & one types
     *
     * @return void
     */
    public function testShapes()
    {
        $key = array_search("shape[x]", array_column($this->parameters, "name"));
        $this->assertCount(15, $this->parameters[$key]['enum']);
        $this->assertContains("circle", $this->parameters[$key]['enum']);
    }

    /**
     * Test size[x] contains correct min and max
     *
     * @return void
     */
    public function testSizes()
    {
        $key = array_search("size[x]", array_column($this->parameters, "name"));
        $this->assertEquals(6, $this->parameters[$key]['minimum']);
        $this->assertEquals(16, $this->parameters[$key]['maximum']);
    }

    /**
     * Test projection enum contains correct count & one names
     *
     * @return void
     */
    public function testProjections()
    {
        $key = array_search("projection", array_column($this->parameters, "name"));
        $this->assertCount(11, $this->parameters[$key]['enum']);
        $this->assertContains("epsg:4326", $this->parameters[$key]['enum']);
    }

    /**
     * Test output enum contains correct count & one names
     *
     * @return void
     */
    public function testOutputs()
    {
        $key = array_search("output", array_column($this->parameters, "name"));
        $this->assertCount(5, $this->parameters[$key]['enum']);
        $this->assertContains("png", $this->parameters[$key]['enum']);
    }
}
