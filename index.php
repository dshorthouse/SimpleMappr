<?php

namespace SimpleMappr;

require __DIR__.'/config/conf.php';
require __DIR__.'/config/conf.db.php';

spl_autoload_register(function ($class){
  $file = __DIR__.'/lib/'.str_replace('SimpleMappr\\', '', $class).'.class.php';
  if(file_exists($file)) { require $file; }
});

$init = new Bootstrap;