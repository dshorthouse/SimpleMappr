<?php

function loader() {
  $files = glob(dirname(dirname(__FILE__)) . '/lib/*.php');
  foreach ($files as $file) {
    require_once($file);
  }
  
  Header::flush_cache(false);
  new Header;
}

spl_autoload_register('loader');