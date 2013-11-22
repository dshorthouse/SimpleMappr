<?php

function switchConf($restore = false) {
  $config_dir = dirname(dirname(__FILE__)) . '/config/';

  $conf = array(
    'prod' => $config_dir . 'conf.php',
    'test' => $config_dir . 'conf.test.php'
  );
  $db = array(
    'prod' => $config_dir . 'conf.db.php',
    'test' => $config_dir . 'conf.db.test.php'
  );

  if(!$restore) {
    if(file_exists($conf['prod'])) { copy($conf['prod'], $conf['prod'] . ".old"); }
    copy($conf['test'], $conf['prod']);
    if(file_exists($db['prod'])) { copy($db['prod'], $db['prod'] . ".old"); }
    copy($db['test'], $db['prod']);
  } else {
    if(file_exists($conf['prod'] . ".old")) { rename($conf['prod'] . ".old", $conf['prod']); }
    if(file_exists($db['prod'] . ".old")) { rename($db['prod'] . ".old", $db['prod']); }
  }

}

function loader() {
  switchConf();

  $files = glob(dirname(dirname(__FILE__)) . '/lib/*.php');
  foreach ($files as $file) {
    require_once($file);
  }

  require_once(__DIR__.'/SimpleMapprTest.php');
  require_once(__DIR__.'/php-webdriver/lib/__init__.php');

  Header::flush_cache(false);
  new Header;

  date_default_timezone_set("America/New_York");
}

spl_autoload_register('loader');

register_shutdown_function(function(){
   switchConf('restore');
});