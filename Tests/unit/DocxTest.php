<?php

/**
 * Unit tests for MapprDocx class
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Mappr\Docx;

class DocxTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $mappr_docx;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->setRequestMethod();
        $this->mappr_docx = new Docx;
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
     * Test that DOCX output has the correct MIME type.
     */
    public function test_docx_mime()
    {
        $this->mappr_docx->execute();
        ob_start();
        $this->mappr_docx->createOutput();
        $output = ob_get_clean();
        $finfo = new finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($output);
        $this->assertEquals("application/zip; charset=binary", $mime);
    }
}
