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
namespace SimpleMappr;

/**
 * Logger for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Logger
{

    /**
     * Log entries
     *
     * @var array $log_entries
     */
    public $entries = [];

    /**
     * Filename for logging
     *
     * @var string $_filename
     */
    private $_filename;

    /**
     * Capture regex for IP addresses and request URL
     *
     * @var string $_capture
     */
    private $_capture = '/(?<ip>(?:\d{1,3}\.){3}\d{1,3}|(?:[a-z0-9]{3,4}\:){7}[a-z0-9]{3,4})(?:.*?)(?<url>\/[api|wms|wfs].*)$/';

    /**
     * Constructor
     *
     * @param string $filename The filename
     */
    public function __construct($filename)
    {
        $this->_filename = $filename;
    }

    /**
     * Write a line to the log file.
     *
     * @param string $message The message to write.
     *
     * @return void
     */
    public function write($message)
    {
        $fd = fopen($this->_filename, 'a');
        fwrite($fd, $message."\n");
        fclose($fd);
    }

    /**
     * 'Tail' the file by specified number of lines.
     *
     * @param integer $n An integer.
     *
     * @return array lines.
     */
    public function tail($n = 10)
    {
        $buffer_size = 1024;
        $input = "";
        $line_count = 0;

        $fp = fopen($this->_filename, 'r');
        if (!$fp) {
            return $this;
        }

        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);

        if (!$pos) {
            return $this;
        }

        while ($line_count < $n + 1) {
            //read the previous block of input
            $read_size = $pos >= $buffer_size ? $buffer_size : $pos;
            fseek($fp, $pos - $read_size, SEEK_SET);

            // prepend the current block, and count the new lines
            $input = fread($fp, $read_size).$input;
            $line_count = substr_count(ltrim($input), "\n");

            //if $pos is == 0 we are at start of file
            $pos -= $read_size;
            if (!$pos) {
                break;
            }
        }

        fclose($fp);
        $this->entries = array_slice(explode("\n", rtrim($input)), -$n);
        return $this;
    }

    public function parse()
    {
        foreach ($this->entries as $key => $log) {
            preg_match($this->_capture, $log, $matches);
            $this->entries[$key] = $matches;
        }
        return $this;
    }
}
