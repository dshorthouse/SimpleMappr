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

  public function log($message) {
    $this->write_log($message);
  }

  public function read_log() {
    $this->check_permission();
    $fd = fopen($this->filename, 'r');
    if ($fd) {
      while (($line = fgets($fd)) !== false) {
        echo $line . "<br>";
      }
    }
    fclose($fd);
  }

  private function write_log($message) {
    $fd = fopen($this->filename, 'a');
    if(is_array($message)) {
      $this->write_array($message, $fd);
    } else {
      $this->write_string($message, $fd);
    }
    fclose($fd);
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

  private function check_permission(){
    session_start();
    if(!isset($_SESSION['simplemappr']) || User::$roles[$_SESSION['simplemappr']['role']] !== 'administrator') {
      Utilities::access_denied();
    }
  }
}