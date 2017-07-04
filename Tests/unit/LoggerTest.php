<?php

/**
 * Unit tests for Logger class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Logger;

class LoggerTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $file;
    protected $logger;

    /**
     * Parent setUp function executed before each test
     */
    protected function setUp()
    {
        $this->file = ROOT."/log/logger.log";
        $this->clearFile();
        $this->logger = new Logger($this->file);
    }

    /**
     * Parent tearDown function executed after each test
     */
    protected function tearDown()
    {
        $this->clearFile();
    }

    /**
     * Empty out the log file.
     */
    private function clearFile()
    {
        file_put_contents($this->file, "");
    }

    /**
     * Get the contents of the log file.
     */
    private function readFile()
    {
        return file_get_contents($this->file);
    }

    /**
     * Test writing to the log file.
     */
    public function test_write()
    {
        $this->logger->write("This is some content");
        $this->assertStringStartsWith("This is some content", $this->readFile());
    }

    /**
     * Test reading from the log file.
     */
    public function test_read()
    {
        $this->logger->write("This is some more content");
        $this->assertStringStartsWith("This is some more content", implode("", $this->logger->tail()));
    }
}
