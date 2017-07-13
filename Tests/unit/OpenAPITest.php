<?php

/**
 * Unit tests for OpenAPI class
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
use SimpleMappr\Controller\OpenApi;

class OpenApiTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $swagger;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $open_api = new OpenApi;
        $this->swagger = $open_api->index();
        $this->parameters = $this->swagger["paths"]["/api"]["get"]["parameters"];
    }

    /**
     * Test that swagger key is produced in OpenAPI.
     */
    public function test_swagger()
    {
        $this->assertArrayHasKey("swagger", $this->swagger);
        $this->assertEquals("2.0", $this->swagger["swagger"]);
    }

    /**
     * Test that info key is produced in OpenAPI.
     */
    public function test_info()
    {
        $this->assertArrayHasKey("info", $this->swagger);
        $this->assertEquals("SimpleMappr API", $this->swagger["info"]["title"]);
    }

    /**
     * Test that host key is produced in OpenAPI.
     */
    public function test_host()
    {
        $this->assertArrayHasKey("host", $this->swagger);
    }

    /**
     * Test that schemes key is produced in OpenAPI.
     */
    public function test_schemes()
    {
        $this->assertArrayHasKey("schemes", $this->swagger);
        $this->assertEquals(["http"], $this->swagger["schemes"]);
    }

    /**
     * Test number of parameters
     */
    public function test_number_parameters()
    {
        $this->assertCount(27, $this->parameters);
    }

    /**
     * Test shape[x] enum contains correct count & one types
     */
    public function test_shapes()
    {
        $key = array_search("shape[x]", array_column($this->parameters, "name"));
        $this->assertCount(15, $this->parameters[$key]['enum']);
        $this->assertContains("circle", $this->parameters[$key]['enum']);
    }

    /**
     * Test projection enum contains correct count & one names
     */
    public function test_projections()
    {
        $key = array_search("projection", array_column($this->parameters, "name"));
        $this->assertCount(11, $this->parameters[$key]['enum']);
        $this->assertContains("epsg:4326", $this->parameters[$key]['enum']);
    }

    /**
     * Test output enum contains correct count & one names
     */
    public function test_outputs()
    {
        $key = array_search("output", array_column($this->parameters, "name"));
        $this->assertCount(5, $this->parameters[$key]['enum']);
        $this->assertContains("png", $this->parameters[$key]['enum']);
    }
}
