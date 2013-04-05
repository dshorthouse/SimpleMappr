<?php
class LOGGER {

  private $filename;

  function __construct($filename) {
    $this->filename = $filename;
  }

  public function log($message) {
    $this->write_log($message);
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
}

?>