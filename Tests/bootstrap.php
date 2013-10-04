<?php

function loader() {
  $files = glob('/Users/dshorthouse/Sites/SimpleMappr/lib/*.php');
  foreach ($files as $file) {
      require_once($file);
  }
}

spl_autoload_register('loader');