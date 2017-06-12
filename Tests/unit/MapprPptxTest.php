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

use PHPUnit\Framework\TestCase;
use SimpleMappr\MapprPptx;

class MapprPptxTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_pptx;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequestMethod();
        $this->mappr_pptx = new MapprPptx;
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown() {
        $this->clearRequestMethod();
        $this->clearTmpFiles();
    }

    /**
     * Test that PPTX output has the correct MIME type.
     */
    public function test_pptx_mime() {
        $this->mappr_pptx->execute();
        ob_start();
        $this->mappr_pptx->createOutput();
        $output = ob_get_clean();
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($output);
        $this->assertEquals("application/vnd.openxmlformats-officedocument.presentationml.presentation; charset=binary", $mime);
    }

}