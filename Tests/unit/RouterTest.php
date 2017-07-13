<?php

/**
 * Unit tests for router handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
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
use \SimpleMappr\Utility;

class RouterTest extends TestCase
{
        use SimpleMapprTestMixin;

        /**
         * Test GET /
         */
        public function testMain_GET()
        {
            $response = $this->httpRequest(MAPPR_URL);
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test GET /about
         */
        public function testAbout_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/about");
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test GET /api
         */
        public function testApi_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/api");
            $this->assertEquals('image/png', $response['mime']);
        }

        /**
         * Test POST /api
         */
        public function testApi_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/api", [], "POST");
            $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        }

        /**
         * Test GET /apidoc
         */
        public function testApidoc_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/apidoc");
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /appplication
         */
        public function testApplication_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/application", [], "POST");
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /appplication.json
         */
        public function testApplicationJson_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/application.json", [], "POST");
            $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /citation
         */
        public function testCitation_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/citation", [], "POST");
            $this->assertEquals(403, $response['code']);
        }

        /**
         * Test GET /citation.json
         */
        public function testCitationJson_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/citation.json");
            $this->assertEquals(403, $response['code']);
        }

        /**
         * Test GET /citation.rss
         */
        public function testCitationRss_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/citation.rss");
            $this->assertEquals('application/xml', $response['mime']);
        }

        /**
         * Test POST /docx
         */
        public function testDocx_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/docx", [], "POST");
            $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $response['mime']);
        }

        /**
         * Test GET /feedback
         */
        public function testFeedback_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/feedback");
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test GET /help
         */
        public function testHelp_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/help");
            $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /kml
         */
        public function testKml_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/kml", [], "POST");
            $this->assertEquals('application/vnd.google-earth.kml+xml kml; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /pptx
         */
        public function testPptx_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/pptx", [], "POST");
            $this->assertEquals('application/vnd.openxmlformats-officedocument.presentationml.presentation', $response['mime']);
        }

        /**
         * Test POST /query
         */
        public function testQuery_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/query", [], "POST");
            $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        }

        /**
         * Test GET /swagger.json
         */
        public function testSwagger_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/swagger.json");
            $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        }

        /**
         * Test POST /usermap
         */
        public function testUsermap_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/usermap", [], "POST");
            $this->assertEquals(403, $response['code']);
        }

        /**
         * Test DELETE /usermap
         */
        public function testUsermap_DELETE()
        {
            $response = $this->httpRequest(MAPPR_URL . "/usermap/1", [], "DELETE");
            $this->assertEquals(403, $response['code']);
        }

        /**
         * Test GET /wfs
         */
        public function testWfs_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/wfs");
            $this->assertEquals('application/xml', $response['mime']);
        }

        /**
         * Test POST /wfs
         */
        public function testWfs_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/wfs", [], "POST");
            $this->assertEquals('application/xml', $response['mime']);
        }

        /**
         * Test GET /wms
         */
        public function testWms_GET()
        {
            $response = $this->httpRequest(MAPPR_URL . "/wms");
            $this->assertEquals('application/xml', $response['mime']);
        }

        /**
         * Test POST /wms
         */
        public function testWms_POST()
        {
            $response = $this->httpRequest(MAPPR_URL . "/wms", [], "POST");
            $this->assertEquals('application/xml', $response['mime']);
        }
}