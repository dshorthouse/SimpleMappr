<?php

/**
 * Unit tests for MapprDocx class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class MapprDocxTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $mappr_docx;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequest();
        $this->mappr_docx = $this->setMapprDefaults(new \SimpleMappr\MapprDocx());
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        $this->clearRequest();
        $this->clearTmpFiles();
    }

    /**
     * Test that DOCX output has the correct MIME type.
     */
    public function test_docx_mime()
    {
        $this->mappr_docx->get_request()->execute();
        ob_start();
        $this->mappr_docx->create_output();
        $output = ob_get_contents();
        ob_end_clean();
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($output);
        $this->assertEquals("application/zip; charset=binary", $mime);
    }

}