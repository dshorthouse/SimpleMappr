<?php

session_start();

function loader() {
  $files = glob(dirname(dirname(__FILE__)) . '/lib/*.php');
  foreach ($files as $file) {
    require_once($file);
  }
}

spl_autoload_register('loader');