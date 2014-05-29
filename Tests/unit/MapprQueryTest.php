<?php

/**
 * Unit tests for static methods and set-up of MapprQuery class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class MapprQueryTest extends PHPUnit_Framework_TestCase
{
    protected $mappr_query;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->mappr_query = new \SimpleMappr\MapprQuery();
        $this->mappr_query->set_shape_path(ROOT."/mapserver/maps")
            ->set_font_file(ROOT."/mapserver/fonts/fonts.list")
            ->set_tmp_path(ROOT."/public/tmp/")
            ->set_tmp_url(MAPPR_MAPS_URL)
            ->set_default_projection("epsg:4326")
            ->set_max_extent("-180,-90,180,90");
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST']);
        $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
        foreach ($tmpfiles as $file) {
            unlink($file);
        }
    }

    /**
     * Test return of country name with query.
     */
    public function testCountry()
    {
        $_REQUEST['bbox_query'] = '176,83,176,83';
        $this->mappr_query->get_request()->execute()->query_layer();
        ob_start();
        $this->mappr_query->create_output();
        $output = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertEquals('Canada', $output[0]);
    }

    /**
     * Test that many country names are returned with a large extent.
     */
    public function testManyCountries()
    {
        $_REQUEST['bbox_query'] = '786,272,900,358';
        $this->mappr_query->get_request()->execute()->query_layer();
        ob_start();
        $this->mappr_query->create_output();
        $output = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertTrue(in_array("Australia",$output));
        $this->assertTrue(in_array("New Zealand",$output));
    }

    /**
     * Test that a StateProvince code in returned when qlayer is provided.
     */
    public function testStateProvince()
    {
        $_REQUEST['bbox_query'] = '176,83,176,83';
        $_REQUEST['qlayer'] = 'stateprovinces_polygon';
        $this->mappr_query->get_request()->execute()->query_layer();
        ob_start();
        $this->mappr_query->create_output();
        $output = json_decode(ob_get_contents(), true);
        ob_end_clean();
        $this->assertEquals('CAN[SK]', $output[0]);
    }

}