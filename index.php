<?php
namespace SimpleMappr;

require __DIR__.'/config/conf.php';
require __DIR__.'/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $file = __DIR__.'/lib/'.str_replace(__NAMESPACE__.'\\', '', $class).'.class.php';
    if (file_exists($file)) {
        require $file;
    }
});

$init = new Bootstrap;