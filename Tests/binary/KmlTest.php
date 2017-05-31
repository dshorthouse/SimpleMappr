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
        $kml = new Kml;
        $kml->getRequest("My Map", $coords);
        $output = $kml->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/kml.kml');
        $this->assertEquals($output, $test_file);
    }

}