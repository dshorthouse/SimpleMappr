<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

use SimpleMappr\Mappr\Application\Map;

/**
 * Test Map Controller for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class MapTest extends SimpleMapprTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Parent setUp function executed before each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->setRequestMethod();
    }

    /**
     * Parent tearDown function executed after each test.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test that the output is png.
     *
     * @return void
     */
    public function testMapPng()
    {
        $mappr_map = new Map(1, "png");
        $mappr_map->execute();
        $file = ROOT."/public/tmp/map_png.png";
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_png.png'));
    }

    /**
     * Test that the output is GeoJSON.
     *
     * @return void
     */
    public function testMapJson()
    {
        $mappr_map = new Map(1, "json");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json.json');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is GeoJSON with polygon.
     *
     * @return void
     */
    public function testMapPolygonJson()
    {
        $mappr_map = new Map(3, "json");
        $mappr_map->execute();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_json_polygon.json');
        $this->assertEquals($this->ob_cleanOutput($mappr_map, true), $test_file);
    }

    /**
     * Test that the output is JPG.
     *
     * @return void
     */
    public function testMapJpg()
    {
        $mappr_map = new Map(1, "jpg");
        $mappr_map->execute();
        $file = ROOT."/public/tmp/map_jpg.jpg";
        file_put_contents($file, $this->ob_cleanOutput($mappr_map, true));
        $this->assertTrue($this->imagesSimilar($file, ROOT.'/Tests/files/map_jpg.jpg'));
    }

    /**
     * Test that the output is KML.
     *
     * @return void
     */
    public function testMapKml()
    {
        $mappr_map = new Map(1, "kml");
        $output = $mappr_map->execute()->createOutput();
        $test_file = file_get_contents(ROOT.'/Tests/files/map_kml.kml');
        $this->assertEquals($output, $test_file);
    }

    /**
     * Test that the output is SVG.
     * NOTE: svg/Imagick tests fail on Travis because they cause a core dump with large SVG files
     *
     * @return void
     */
    /*
    public function testMapSvg()
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
     *
     * @return void
     */
    public function testMapLegend()
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
     *
     * @return void
     */
    public function testMapNolegend()
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
