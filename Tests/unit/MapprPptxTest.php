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
    use SimpleMapprMixin;

    protected $mappr_pptx;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->mappr_pptx = $this->setMapprDefaults(new \SimpleMappr\MapprPptx());
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown() {
        $this->clearRequest();
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