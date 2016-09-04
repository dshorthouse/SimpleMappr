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
class MapprApiTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $mappr_api;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequest();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
        $this->clearTmpFiles();
    }

    /**
     * Test that a ping request is produced.
     */
    public function test_api_ping()
    {
        $_REQUEST = array('ping' => true);
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $decoded = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertArrayHasKey("status", $decoded);
        unset($_REQUEST);
    }

    /**
     * Test that a parameters request is produced.
     */
    public function test_api_parameters()
    {
        $_REQUEST = array('parameters' => true);
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $decoded = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertArrayHasKey("zoom", $decoded);
        unset($_REQUEST);
    }

    /**
     * Test that a simple POST request is handled.
     */
    public function test_apioutput_post()
    {
        $this->setRequest('POST');
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $decoded = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertArrayHasKey("imageURL", $decoded);
        $this->assertArrayHasKey("expiry", $decoded);
        $this->assertContains(MAPPR_MAPS_URL, $decoded["imageURL"]);
        $image = file_get_contents($decoded["imageURL"]);
        $this->assertEquals("\x89PNG\x0d\x0a\x1a\x0a",substr($image,0,8));
    }

    /**
     * Test that a simple GET request is handled.
     */
    public function test_apioutput_get()
    {
        $_REQUEST = array();
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_get.png';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_get.png'));
    }

    /**
     * Test that a few API request parameters are handled.
     */
    public function test_apioutput_get_params()
    {
        $_REQUEST = array(
            'bbox' => '-130,40,-60,50',
            'projection' => 'esri:102009',
            'width' => 600,
            'graticules' => true
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_get_params.png';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_get_params.png'));
    }

    /**
     * Test API response in produced when coordinates are not supplied.
     */
    public function test_apioutput_no_coords()
    {
        $_REQUEST = array(
            'points' => array()
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_no_coords.png';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_no_coords.png'));
    }

    /**
     * Test API response when coordinates are supplied.
     */
    public function test_apioutput_coords()
    {
        $_REQUEST = array(
            'points' => array("45, -120\n52, -100")
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_coords.png';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_coords.png'));
    }

    /**
     * Test API response to ensure that "Québec" is properly encoded.
     */
    public function test_apioutput_encoding()
    {
        $_REQUEST = array(
            'bbox' => '-91.9348552339,38.8500000000,-47.2856347438,61.3500000000',
            'layers' => 'stateprovnames'
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT."/public/tmp/apioutput_encoding.png";
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_encoding.png'));
    }

    /**
     * Test API response to ensure that regions get shaded.
     */
    public function test_apioutput_country()
    {
        $_REQUEST = array(
            'shade' => array(
                'places' => 'Alberta,USA[MT|WA]'
            )
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_places.png';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_places.png'));
    }

    /**
     * Test API response to ensure that ecoregions get shaded.
     */
    public function test_apioutput_ecoregions()
    {
        if (!array_key_exists('TRAVIS', $_SERVER)) {
            $_REQUEST = array(
                'layers' => 'ecoregions'
            );
            $mappr_api = new \SimpleMappr\MapprApi();
            $mappr_api->execute();
            ob_start();
            echo $mappr_api->createOutput();
            $output = ob_get_contents();
            $file = ROOT.'/public/tmp/apioutput_ecoregions.png';
            file_put_contents($file, $output);
            ob_end_clean();
            $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_ecoregions.png'));
        }
    }

    /**
     * Test API response to ensure that a tif can be produced.
     */
    public function test_apioutput_tif()
    {
        $_REQUEST = array(
            'output' => 'tif',
            'shade' => array(
                'places' => 'Alberta,USA[MT|WA]'
            )
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_tif.tif';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_tif.tif'));
    }

    /**
     * Test API response to ensure that a tif can be produced.
     */
    public function test_apioutput_svg()
    {
        $_REQUEST = array(
            'output' => 'svg',
            'shade' => array(
                'places' => 'Alberta,USA[MT|WA]'
            )
        );
        $mappr_api = new \SimpleMappr\MapprApi();
        $mappr_api->execute();
        ob_start();
        echo $mappr_api->createOutput();
        $output = ob_get_contents();
        $file = ROOT.'/public/tmp/apioutput_svg.svg';
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/apioutput_svg.svg'));
    }
}