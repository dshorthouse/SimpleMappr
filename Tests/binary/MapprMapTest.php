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
    public function setUp()
    {
        $this->mappr_map = "";
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test that the output is png.
     */
    public function test_map_png()
    {
        $mappr_map = new MapprMap(1, "png");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/map_png.png";
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }

    /**
     * Test that the output is GeoJSON.
     */
    public function test_map_json()
    {
        $mappr_map = new MapprMap(1, "json");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/map_json.json";
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/map_json.json'));
    }

    /**
     * Test that the output is GeoJSON with polygon.
     */
/*
    public function test_map_polygon_json()
    {
        $mappr_map = new MapprMap(3, "json");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/map_json_polygon.json";
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/map_json_polygon.json'));
    }
*/
    /**
     * Test that the output is SVG.
     */
    public function test_map_svg()
    {
        $mappr_map = new MapprMap(1, "svg");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $svgfile = ROOT."/public/tmp/map_svg.svg";
        file_put_contents($svgfile, $output);
        $image1 = new \Imagick($svgfile);
        $image1->setImageFormat('png');
        $file = ROOT.'/public/tmp/map_svg.png';
        $image1->writeImage($file);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_svg.png'));
    }

    /**
     * Test that the output is KML.
     */
    public function test_map_kml()
    {
        $mappr_map = new MapprMap(1, "kml");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT."/public/tmp/map_kml.kml";
        file_put_contents($file, $output);
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/map_kml.kml'));
    }

    /**
     * Test that the output has a legend.
     */
    public function test_map_legend()
    {
        $this->setRequestMethod();
        $req = ['legend' => 'true'];
        $this->setRequest($req);
        $mappr_map = new MapprMap(1, "png");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/map_png_legend.png';
        file_put_contents($file, $output);
        $this->setRequest([]);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png_legend.png'));
    }

    /**
     * Test that the output does not have a legend.
     */
    public function test_map_nolegend()
    {
        $this->setRequestMethod();
        $req = ['legend' => 'false'];
        $this->setRequest($req);
        $mappr_map = new MapprMap(1, "png");
        $mappr_map->execute();
        ob_start();
        $mappr_map->createOutput();
        $output = ob_get_clean();
        $file = ROOT.'/public/tmp/map_png_nolegend.png';
        file_put_contents($file, $output);
        $this->setRequest([]);
        $this->assertTrue(SimpleMapprTest::imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }
}