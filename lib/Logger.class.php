<?php

/********************************************************************

Logger.class.php released under MIT License
Log API calls for SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

namespace SimpleMappr;

class Logger {

  private $filename;

  function __construct($filename) {
    $this->filename = $filename;
  }

  public function write($message) {
    $fd = fopen($this->filename, 'a');
    if(is_array($message)) {
      $this->write_array($message, $fd);
    } else {
      $this->write_string($message, $fd);
    }
    fclose($fd);
  }

  public function tail($n = 10) {
    User::check_permission();
    $buffer_size = 1024;
    $input = '';
    $line_count = 0;

    $fp = fopen($this->filename, 'r');
    if (!$fp) { return array(); }

    fseek($fp, 0, SEEK_END);
    $pos = ftell($fp);

    if(!$pos) { return array(); }

    while ($line_count < $n + 1) {
      // read the previous block of input
      $read_size = $pos >= $buffer_size ? $buffer_size : $pos;
      fseek($fp, $pos - $read_size, SEEK_SET);

      // prepend the current block, and count the new lines
      $input = fread($fp, $read_size).$input;
      $line_count = substr_count(ltrim($input), "\n");

      // if $pos is == 0 we are at start of file
      $pos -= $read_size;
      if (!$pos) break;
    }

    fclose($fp);
    return array_slice(explode("\n", rtrim($input)), -$n);
  }

  private function write_string($message, $fd)  {
    fwrite($fd, $message."\n");
  }

  private function write_array($message, $fd) {
    foreach($message as $key => $value) {
      if(is_array($value)) {
        fwrite($fd, $key."{ ");
        $this->write_array($value, $fd);
        fwrite($fd, " }\n");
      } else {
        $string =  "\t {".$key.': '.$value."}\n ";
        fwrite($fd, $string);
      }
    }
  }

}