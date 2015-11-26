<?php

/**
 * Unit tests for WFS class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class WfsTest extends PHPUnit_Framework_TestCase
{
    use SimpleMapprMixin;

    protected $wfs;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->wfs = new \SimpleMappr\MapprWfs();
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
     * Test that GetCapabilities request is handled.
     */
    public function test_wfs_getcapabilities()
    {
        $_REQUEST = array();
        $this->wfs->wfs_layers = array('lakes' => 'on');
        $this->wfs->getRequest()->makeService()->execute();
        ob_start();
        echo $this->wfs->createOutput();
        $output = ob_get_contents();
        $file = ROOT."/public/tmp/wfs.xml";
        file_put_contents($file, $output);
        ob_end_clean();
        $this->assertTrue(SimpleMapprTest::filesIdentical($file, ROOT.'/Tests/files/wfs.xml'));
    }

}