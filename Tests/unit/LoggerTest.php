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

/**
 * Test Logger class for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class LoggerTest extends TestCase
{
    use SimpleMapprTestMixin;

    protected $file;
    protected $logger;
    protected $capture;

    /**
     * Parent setUp function executed before each test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->file = ROOT."/log/logger.log";
        $this->_clearFile();
        $this->logger = new Logger($this->file);
    }

    /**
     * Parent tearDown function executed after each test
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->_clearFile();
    }

    /**
     * Empty out the log file.
     *
     * @return void
     */
    private function _clearFile()
    {
        file_put_contents($this->file, "");
    }

    /**
     * Get the contents of the log file.
     *
     * @return void
     */
    private function _readFile()
    {
        return file_get_contents($this->file);
    }

    /**
     * Test writing to the log file.
     *
     * @return void
     */
    public function testWrite()
    {
        $this->logger->write("This is some content");
        $this->assertStringStartsWith("This is some content", $this->_readFile());
    }

    /**
     * Test reading from the log file.
     *
     * @return void
     */
    public function testRead()
    {
        $this->logger->write("This is some more content");
        $log_data = implode("", $this->logger->tail()->entries);
        $this->assertStringStartsWith("This is some more content", $log_data);
    }

    /**
     * Test entries is an empty array.
     *
     * @return void
     */
    public function testBlank()
    {
        $entries = $this->logger->tail()->parse()->entries;
        $this->assertEmpty($entries);
    }

    /**
     * Test parsing the log file for IPv4 addresses.
     *
     * @return void
     */
    public function testIPv4()
    {
        $data  = "2017-10-23 12:56:59 - 200.62.146.210 - API - POST - /api/?projection=epsg:4396";
        $this->logger->write($data);
        $ip = $this->logger->tail()->parse(Logger::$capture)->entries[0]["ip"];
        $this->assertEquals("200.62.146.210", $ip);
    }

    /**
     * Test parsing the log file for IPv6 addresses.
     *
     * @return void
     */
    public function testIPv6a()
    {
        $data = "2017-10-23 12:56:59 - 2607:ea00:107:802:4841:852c:1245:6f47 - API - POST - /api/?projection=epsg:54030";
        $this->logger->write($data);
        $ip = $this->logger->tail()->parse(Logger::$capture)->entries[0]["ip"];
        $this->assertEquals("2607:ea00:107:802:4841:852c:1245:6f47", $ip);
    }

    /**
     * Test parsing the log file for IPv6 addresses.
     *
     * @return void
     */
    public function testIPv6b()
    {
        $data = "2017-10-23 12:56:59 - 2607:ea00:107a:802a:4841:852c:1245:6f47 - API - POST - /api/?projection=epsg:54030";
        $this->logger->write($data);
        $ip = $this->logger->tail()->parse(Logger::$capture)->entries[0]["ip"];
        $this->assertEquals("2607:ea00:107a:802a:4841:852c:1245:6f47", $ip);
    }

    /**
     * Test parsing the log file for URL.
     *
     * @return void
     */
    public function testURL()
    {
        $data  = "2017-10-23 12:56:59 - 200.62.146.210 - API - POST - /api/?projection=epsg:4396";
        $this->logger->write($data);
        $url = $this->logger->tail()->parse(Logger::$capture)->entries[0]["url"];
        $this->assertEquals("/api/?projection=epsg:4396", $url);
    }

    /**
     * Test parsing the log file for the time.
     *
     * @return void
     */
    public function testTime()
    {
        $data  = "2017-10-23 12:56:59 - 200.62.146.210 - API - POST - /api/?projection=epsg:4396";
        $this->logger->write($data);
        $time = $this->logger->tail()->parse(Logger::$capture)->entries[0]["time"];
        $this->assertEquals("2017-10-23 12:56:59", $time);
    }

    /**
     * Test parsing the log file for the type of API request.
     *
     * @return void
     */
    public function testType()
    {
        $data  = "2017-10-23 12:56:59 - 200.62.146.210 - API - POST - /api/?projection=epsg:4396";
        $this->logger->write($data);
        $type = $this->logger->tail()->parse(Logger::$capture)->entries[0]["type"];
        $this->assertEquals("API", $type);
    }

    /**
     * Test parsing the log file for the request method.
     *
     * @return void
     */
    public function testMethod()
    {
        $data  = "2017-10-23 12:56:59 - 200.62.146.210 - API - POST - /api/?projection=epsg:4396";
        $this->logger->write($data);
        $method = $this->logger->tail()->parse(Logger::$capture)->entries[0]["method"];
        $this->assertEquals("POST", $method);
    }
}
