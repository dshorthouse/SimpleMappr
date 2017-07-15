<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
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

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\Application;
use SimpleMappr\Utility;

class ApplicationTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr;
    protected $output;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $mappr = new Application;
        $this->mappr = $mappr->execute();
        $this->output = json_decode($this->mappr->createOutput(), true);
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
     * Test that empty lines are removed.
     */
    public function test_removeEmptyLines()
    {
        $data = "\n\n45.0\t-120.0\n\n\n\n\n55.0\t-110.0\n\n\n60.0 -100.0\n\n\n";
        $removed_lines = Utility::removeEmptyLines($data);
        $this->assertEquals($removed_lines, "\n45.0\t-120.0\n55.0\t-110.0\n60.0 -100.0\n");
    }

    /**
     * Test that slashes are added.
     */
    public function test_addSlashesExtended()
    {
        $data = [['title' => 'my "title"']];
        $add_slashes = Utility::addSlashesExtended($data);
        $this->assertEquals($add_slashes[0]['title'], "my \\\"title\\\"");
    }

    /**
     * Test that a file name is cleaned of cruft.
     */
    public function test_clean_filename()
    {
        $name = "My %!  <>  .  Map";
        $clean = Utility::cleanFilename($name);
        $this->assertEquals($clean, "My_Map");
    }

    /**
     * Test that the mappr object returns the declared font file path.
     */
    public function test_font_file()
    {
        $this->assertEquals($this->mappr->get_font_file(), ROOT."/mapserver/fonts/fonts.list");
    }

    /**
     * Test that the mappr object returns the declared tmp path.
     */
    public function test_tmp_path()
    {
        $this->assertEquals($this->mappr->get_tmp_path(), ROOT."/public/tmp/");
    }

    /**
     * Test that the mappr object returns the declared tmp url.
     */
    public function test_tmp_url()
    {
        $this->assertEquals($this->mappr->get_tmp_url(), MAPPR_MAPS_URL);
    }

    /**
     * Test that the default max extent is the entire world.
     */
    public function test_max_extent()
    {
        $diff = array_diff($this->mappr->get_max_extent(), [-180, -90, 180, 90]);
        $this->assertEmpty($diff);
    }

    /**
     * Test that the default projection is EPSG:4326.
     */
    public function test_default_projection()
    {
        $this->assertEquals($this->mappr->get_default_projection(), "epsg:4326");
    }

    /**
     * Test that the default set of coords is an empty array.
     */
    public function test_default_coords()
    {
        $this->assertEmpty($this->mappr->request->coords);
    }

    /**
     * Test that the default set of regions is an empty array.
     */
    public function test_default_regions()
    {
        $this->assertEmpty($this->mappr->request->regions);
    }

    /**
     * Test that the default set of wkt is an empty array.
     */
    public function test_default_wkt()
    {
        $this->assertEmpty($this->mappr->request->wkt);
    }

    /**
     * Test that the default output format is png.
     */
    public function test_default_output()
    {
        $this->assertEquals($this->mappr->request->output, "png");
    }

    /**
     * Test that the default width is 900px.
     */
    public function test_default_width()
    {
        $this->assertEquals($this->mappr->request->width, 900);
    }

    /**
     * Test that default height is 450px.
     */
    public function test_default_height()
    {
        $this->assertEquals($this->mappr->request->height, 450);
    }

    /**
     * Test that default map projection is EPSG:4326.
     */
    public function test_default_projection_map()
    {
        $this->assertEquals($this->mappr->request->projection_map, "epsg:4326");
    }

    /**
     * Test that the default longitude of natural origin is 0.
     */
    public function test_default_origin()
    {
        $this->assertEquals($this->mappr->request->origin, 0);
    }

    /**
     * Test that the default rubberband is an empty array.
     */
    public function test_default_bbox_rubberband()
    {
        $this->assertEmpty($this->mappr->request->bbox_rubberband);
    }

    /**
     * Test that default pan is blank.
     */
    public function test_default_pan()
    {
        $this->assertEquals($this->mappr->request->pan, "");
    }

    /**
     * Test that only the base layer is initially rendered.
     */
    public function test_default_layers()
    {
        $layers = $this->mappr->request->layers;
        $this->assertEmpty(array_diff($layers, ['base' => 'on']));
    }

    /**
     * Test that the default graticule selection is blank.
     */
    public function test_default_graticules()
    {
        $this->assertEquals($this->mappr->request->graticules, "");
    }

    /**
     * Test that the default watermark selection is blank.
     */
    public function test_default_watermark()
    {
        $this->assertEquals($this->mappr->request->watermark, "");
    }

    /**
     * Test that the default graticule grid is blank.
     */
    public function test_default_gridspace()
    {
        $this->assertEquals($this->mappr->request->gridspace, "");
    }

    /**
     * Test that the default graticule label is 1.
     */
    public function test_default_gridlabel()
    {
        $this->assertEquals($this->mappr->request->gridlabel, 1);
    }

    /**
     * Test that the default download selection is blank.
     */
    public function test_default_download()
    {
        $this->assertEquals($this->mappr->request->download, "");
    }

    /**
     * Test that the default crop is blank.
     */
    public function test_default_crop()
    {
        $this->assertEquals($this->mappr->request->crop, "");
    }

    /**
     * Test that the default options is an empty array.
     */
    public function test_default_options()
    {
        $this->assertEmpty($this->mappr->request->options);
    }

    /**
     * Test that the default border thickness is 1.25.
     */
    public function test_default_border_thickness()
    {
        $this->assertEquals($this->mappr->request->border_thickness, 1.25);
    }

    /**
     * Test that the default rotation is 0.
     */
    public function test_default_rotation()
    {
        $this->assertEquals($this->mappr->request->rotation, 0);
    }

    /**
     * Test that the default zoom is blank.
     */
    public function test_default_zoom_out()
    {
        $this->assertEquals($this->mappr->request->zoom_out, "");
    }

    /**
     * Test that the default image URL ends with .png.
     */
    public function test_default_image_url()
    {
        $this->assertStringEndsWith(".png", $this->mappr->image_url);
    }

    /**
     * Test that the default application output contains all necessary keys.
     */
    public function test_mapserver_output_contains_all_keys()
    {
        $this->assertArrayHasKey("mapOutputImage", $this->output);
        $this->assertArrayHasKey("size", $this->output);
        $this->assertArrayHasKey("rendered_bbox", $this->output);
        $this->assertArrayHasKey("rendered_rotation", $this->output);
        $this->assertArrayHasKey("rendered_projection", $this->output);
        $this->assertArrayHasKey("legend_url", $this->output);
        $this->assertArrayHasKey("scalebar_url", $this->output);
        $this->assertArrayHasKey("bad_points", $this->output);
        $this->assertArrayHasKey("bad_drawings", $this->output);
    }

    /**
     * Test that the scalebar URL is populated in the application response.
     */
    public function test_scalebar_url_exists()
    {
        $this->assertNotEmpty($this->output["scalebar_url"]);
    }

    /**
     * Test that the default legend URL is empty.
     */
    public function test_legend_url_empty()
    {
        $this->assertEmpty($this->output["legend_url"]);
    }

    /**
     * Test that a file exists based on the application response.
     */
    public function test_file_exists()
    {
        $img = $this->mappr->get_tmp_path() . basename($this->output["mapOutputImage"]);
        $this->assertFileExists($img);
    }

    /**
     * Test file is accessible from web server
     */
     public function test_file_accessible()
     {
         $this->assertContains(MAPPR_MAPS_URL, $this->output["mapOutputImage"]);
         $image = file_get_contents($this->output["mapOutputImage"]);
         $finfo = new \finfo(FILEINFO_MIME_TYPE);
         $this->assertEquals("image/png", $finfo->buffer($image));
     }

    /**
     * Test that the indicated size in the application response is 900X450.
     */
    public function test_mapserver_default_size()
    {
        $diff = array_diff($this->output["size"], [900, 450]);
        $this->assertEmpty($diff);
    }

    /**
     * Test that the application response shows a bbox for the entire world.
     */
    public function test_mapserver_default_rendered_bbox()
    {
        $this->assertEquals($this->output["rendered_bbox"], "-180.0000000000,-90.0000000000,180.0000000000,90.0000000000");
    }

    /**
     * Test that the rendered rotation as indicated is 0.
     */
    public function test_mapserver_default_rendered_rotation()
    {
        $this->assertEquals($this->output["rendered_rotation"], 0);
    }

    /**
     * Test that the rendered projection as indicated is EPSG:4326.
     */
    public function test_mapserver_rendered_projection()
    {
        $this->assertEquals($this->output["rendered_projection"], "epsg:4326");
    }

    /**
     * Test that the default array of bad points is blank.
     */
    public function test_mapserver_default_bad_points()
    {
        $this->assertEquals($this->output["bad_points"], "");
    }

    /**
     * Test that the default array of bad points is blank.
     */
    public function test_mapserver_default_bad_drawings()
    {
        $this->assertEquals($this->output["bad_drawings"], "");
    }
}
