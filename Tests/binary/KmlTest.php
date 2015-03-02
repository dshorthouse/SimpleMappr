<?php

/**
 * Unit tests for static methods and set-up of Kml class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class KmlTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $kml;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->kml = new \SimpleMappr\Kml();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        session_destroy(); //req'd because Kml class sets a cookie
        $this->clearRequest();
        $this->clearTmpFiles();
    }

    /**
     * Test production of KML.
     */
    public function test_kml()
    {
        $coords = array(
            array(
                'title' => 'Sample Data',
                'data' => "55, -115\n65, -110",
                'shape' => 'star',
                'size' => 14,
                'color' => '255 32 3'
            ),
            array(
                'title' => 'Sample Data2',
                'data' => "35, -120\n70, -80",
                'shape' => 'circle',
                'size' => 14,
                'color' => '255 32 3'
            )
        );
        $this->kml->getRequest("My Map", $coords);
        ob_start();
        $this->kml->createOutput();
        $output = ob_get_contents();
        $file = ROOT."/public/tmp/kml.kml";
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/kml.kml'));
    }

}