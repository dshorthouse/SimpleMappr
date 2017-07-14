<?php

/**
 * Unit tests for static methods and set-up of Api class
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
use SimpleMappr\Mappr\Api;

class ApiTest extends TestCase
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
     * Test that a ping request is produced.
     */
    public function test_api_ping()
    {
        $this->setRequest(['ping' => true]);
        $mappr_api = new Api;
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
        $mappr_api = new Api;
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
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_get.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_get.png'));
    }

    /**
     * Test that a few API request parameters are handled.
     */
    public function test_apioutput_get_params()
    {
        $req = [
            'bbox' => '-130,40,-60,50',
            'projection' => 'esri:102009',
            'width' => 600,
            'graticules' => true
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_get_params.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_get_params.png'));
    }

    /**
     * Test API response in produced when coordinates are not supplied.
     */
    public function test_apioutput_no_coords()
    {
        $req = [
            'points' => []
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_no_coords.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_no_coords.png'));
    }

    /**
     * Test API response when coordinates are supplied.
     */
    public function test_apioutput_coords()
    {
        $req = [
            'points' => ["45, -120\n52, -100"]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_coords.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_coords.png'));
    }

    /**
     * Test API response to ensure that "Québec" is properly encoded.
     */
    public function test_apioutput_encoding()
    {
        $req = [
            'bbox' => '-91.9348552339,38.8500000000,-47.2856347438,61.3500000000',
            'layers' => 'stateprovnames'
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/apioutput_encoding.png";
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_encoding.png'));
    }

    /**
     * Test API response to ensure that regions get shaded.
     */
    public function test_apioutput_country()
    {
        $req = [
            'shade' => [
                'places' => 'Alberta,USA[MT|WA]'
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_places.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_places.png'));
    }

    /**
     * Test API response to ensure that a tif can be produced.
     */
    public function test_apioutput_tif()
    {
        $req = [
            'output' => 'tif',
            'shade' => [
                'places' => 'Alberta,USA[MT|WA]'
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_tif.tif';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_tif.tif'));
    }

    /**
     * Test API response to ensure that svg can be produced.
     */
    public function test_apioutput_svg()
    {
        $req = [
            'output' => 'svg',
            'shade' => [
                'places' => 'CAN[SK]'
            ],
            'bbox' => '-109,50,-105,58',
            'width' => 200,
            'height' => 275,
            'watermark' => 'false'
        ];

        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();

        ob_start();
        $mappr_api->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/apioutput_svg.svg';
        file_put_contents($file, $output);

        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_svg.svg'));
    }

    /**
     * Test API response to ensure that image can be produced using WKT parameter.
     */
    public function test_apioutput_wkt()
    {
        $req = [
            'wkt' => [
                0 => [
                    'data' => 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))'
                ]
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        $level = ob_get_level();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        if (ob_get_level() > $level) {
            ob_end_clean();
        }
        $file = ROOT.'/public/tmp/apioutput_wkt.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_wkt.png'));
    }

    /**
     * Test API response to ensure that image can be produced using WKT parameter.
     */
    public function test_apioutput_wkt_border()
    {
        $req = [
            'wkt' => [
                0 => [
                    'data' => 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))',
                    'border' => true
                ]
            ]
        ];
        $this->setRequest($req);
        $mappr_api = new Api;
        $mappr_api->execute();
        ob_start();
        $level = ob_get_level();
        echo $mappr_api->createOutput();
        $output = ob_get_clean();
        if (ob_get_level() > $level) {
            ob_end_clean();
        }
        $file = ROOT.'/public/tmp/apioutput_wkt_border.png';
        file_put_contents($file, $output);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/apioutput_wkt_border.png'));
    }
}
