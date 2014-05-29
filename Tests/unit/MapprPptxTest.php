<?php

/**
 * Unit tests for MapprPptx class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class MapprPptxTest extends PHPUnit_Framework_TestCase
{
    protected $mappr_api;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->mappr_pptx = new \SimpleMappr\MapprPptx();
        $this->mappr_pptx->set_shape_path(ROOT."/mapserver/maps")
            ->set_font_file(ROOT."/mapserver/fonts/fonts.list")
            ->set_tmp_path(ROOT."/public/tmp/")
            ->set_tmp_url(MAPPR_MAPS_URL)
            ->set_default_projection("epsg:4326")
            ->set_max_extent("-180,-90,180,90");
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown() {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST']);
        $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
        foreach ($tmpfiles as $file) {
            unlink($file);
        }
    }

    /**
     * Test that PPTX output has the correct MIME type.
     */
    public function test_pptx_mime() {
        $this->mappr_pptx->get_request()->execute();
        ob_start();
        $this->mappr_pptx->create_output();
        $output = ob_get_contents();
        ob_end_clean();
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($output);
        $this->assertEquals("application/vnd.openxmlformats-officedocument.presentationml.presentation; charset=binary", $mime);
    }

}