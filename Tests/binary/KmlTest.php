<?php

/**
 * Unit tests for static methods and set-up of Kml class
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Kml;

class KmlTest extends TestCase
{
    use SimpleMapprMixin;

    protected $kml;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequestMethod();
        $this->kml = new Kml;
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
        }
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test production of KML.
     */
    public function test_kml()
    {
        $coords = [
            [
                'title' => 'Sample Data',
                'data' => "55, -115\n65, -110",
                'shape' => 'star',
                'size' => 14,
                'color' => '255 32 3'
            ],
            [
                'title' => 'Sample Data2',
                'data' => "35, -120\n70, -80",
                'shape' => 'circle',
                'size' => 14,
                'color' => '255 32 3'
            ]
        ];
        $this->kml->getRequest("My Map", $coords);
        ob_start();
        $this->kml->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/kml.kml";
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/kml.kml'));
    }

}