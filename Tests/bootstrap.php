<?php

date_default_timezone_set('America/New_York');

define("COOKIE_TIMEOUT", time() + (2 * 7 * 24 * 60 * 60));

session_start();

function loader() {
  $files = glob(dirname(dirname(__FILE__)) . '/lib/*.php');
  foreach ($files as $file) {
    require_once($file);
  }
}

spl_autoload_register('loader');