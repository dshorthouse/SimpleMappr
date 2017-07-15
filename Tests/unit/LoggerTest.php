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
