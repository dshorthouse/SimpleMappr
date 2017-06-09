<?php

/**
 * Unit tests for static methods and set-up of MapprMap class
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use SimpleMappr\MapprMap;

class MapprMapTest extends SimpleMapprTest
{
    use SimpleMapprMixin;

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

    public function getOutputBuffer($mappr)
    {
        $mappr->execute();
        $level = ob_get_level();
        ob_start();
        $mappr->createOutput();
        $output = ob_get_clean();
        if (ob_get_level() > $level) { ob_end_clean(); }
        return $output;
    }

    /**
     * Test that the output is png.
     */
    public function test_map_png()
    {
        $mappr_map = new MapprMap(1, "png");
        $file = ROOT."/public/tmp/map_png.png";
        file_put_contents($file, $this->getOutputBuffer($mappr_map));
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }

    /**
     * Test that the output is GeoJSON.
     */
    public function test_map_json()
    {
        $mappr_map = new MapprMap(1, "json");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json.json');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is GeoJSON with polygon.
     */
    public function test_map_polygon_json()
    {
        $mappr_map = new MapprMap(3, "json");
        //get outputbuffer level because geoPHP::load in MapprMap creates an unwanted stream
        $level = ob_get_level();
        $output = $mappr_map->execute()->createOutput();
        if (ob_get_level() > $level) { ob_end_clean(); }
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json_polygon.json');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is SVG.
     */
/*
    public function test_map_svg()
    {
        $mappr_map = new MapprMap(1, "svg");
        $file = ROOT."/public/tmp/map_svg.svg";
        file_put_contents($file, $this->getOutputBuffer($mappr_map));
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_svg.svg'));
    }
*/

    /**
     * Test that the output is KML.
     */
    public function test_map_kml()
    {
        $mappr_map = new MapprMap(1, "kml");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_kml.kml');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output has a legend.
     */
    public function test_map_legend()
    {
        $req = ['legend' => 'true'];
        $this->setRequest($req);
        $mappr_map = new MapprMap(1, "png");
        $file = ROOT.'/public/tmp/map_png_legend.png';
        file_put_contents($file, $this->getOutputBuffer($mappr_map));
        $this->setRequest([]);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png_legend.png'));
    }

    /**
     * Test that the output does not have a legend.
     */
    public function test_map_nolegend()
    {
        $req = ['legend' => 'false'];
        $this->setRequest($req);
        $mappr_map = new MapprMap(1, "png");
        $file = ROOT.'/public/tmp/map_png_nolegend.png';
        file_put_contents($file, $this->getOutputBuffer($mappr_map));
        $this->setRequest([]);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }
}