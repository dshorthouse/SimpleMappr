<?php

/**
 * Unit tests for static methods and set-up of Kml class
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
use SimpleMappr\Controller\Kml;

class KmlTest extends TestCase
{
    use SimpleMapprTestMixin;

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
        $content = [
            "file_name" => "My_Map",
            "coords" => [
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
            ]
        ];
        $kml = (new Kml)->create($content);
        $test_file = file_get_contents(ROOT.'/Tests/files/kml.kml');
        $this->assertEquals($kml, $test_file);
    }
}
