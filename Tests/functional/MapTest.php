<?php

/**
 * Unit tests for static methods and set-up of Map class
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */

use SimpleMappr\Mappr\Map;

class MapTest extends SimpleMapprFunctionalTestCase
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
     * Test that the output is png.
     */
    public function test_map_png()
    {
        $mappr_map = new Map(1, "png");
        $mappr_map->execute();
        $file = ROOT."/public/tmp/map_png.png";
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }

    /**
     * Test that the output is GeoJSON.
     */
    public function test_map_json()
    {
        $mappr_map = new Map(1, "json");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json.json');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is GeoJSON with polygon.
     */
    public function test_map_polygon_json()
    {
        $mappr_map = new Map(3, "json");
        $mappr_map->execute();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json_polygon.json');
        $this->assertEquals($this->ob_cleanOutput($mappr_map, true), $test_file);
    }

    /**
     * Test that the output is JPG.
     */
    public function test_map_jpg()
    {
        $mappr_map = new Map(1, "jpg");
        $mappr_map->execute();
        $file = ROOT."/public/tmp/map_jpg.jpg";
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_jpg.jpg'));
    }

    /**
     * Test that the output is KML.
     */
    public function test_map_kml()
    {
        $mappr_map = new Map(1, "kml");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_kml.kml');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is SVG.
     * NOTE: svg/Imagick tests fail on Travis because they cause a core dump with large SVG files
     */
/*
    public function test_map_svg()
    {
        $mappr_map = new Map(1, "svg");
        $mappr_map->execute();
        $file = ROOT."/public/tmp/map_svg.svg";
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_svg.svg'));
    }
*/

    /**
     * Test that the output has a legend.
     */
    public function test_map_legend()
    {
        $req = ['legend' => 'true'];
        $this->setRequest($req);
        $mappr_map = new Map(1, "png");
        $mappr_map->execute();
        $file = ROOT.'/public/tmp/map_png_legend.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->setRequest([]);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_png_legend.png'));
    }

    /**
     * Test that the output does not have a legend.
     */
    public function test_map_nolegend()
    {
        $req = ['legend' => 'false'];
        $this->setRequest($req);
        $mappr_map = new Map(1, "png");
        $mappr_map->execute();
        $file = ROOT.'/public/tmp/map_png_nolegend.png';
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->setRequest([]);
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }
}
