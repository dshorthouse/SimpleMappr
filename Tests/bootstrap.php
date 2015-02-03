<?php

/**
 * Bootstrapper for executing PHP Unit tests
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

date_default_timezone_set("America/New_York");

/**
 * Switch configuration files
 *
 * @param bool $restore Flag to toggle swap and replacement of config files
 * @return void
 */
function switchConf($restore = false)
{
    $config_dir = dirname(__DIR__) . '/config/';

    $conf = array(
        'prod' => $config_dir . 'conf.php',
        'test' => $config_dir . 'conf.test.php'
    );

    if (!$restore) {
        if (!file_exists($conf['prod'] . ".old")) {
            if (file_exists($conf['prod'])) {
                copy($conf['prod'], $conf['prod'] . ".old");
            }
            copy($conf['test'], $conf['prod']);
        }
    } else {
        if (file_exists($conf['prod'] . ".old")) {
            rename($conf['prod'] . ".old", $conf['prod']);
        }
    }

}

/**
 * Require all files necessary to execute tests
 *
 * @return void
 */
function requireFiles()
{
    $root = dirname(__DIR__);

    require_once $root . '/config/conf.php';

    $rest = $root . '/lib/RestMethods.class.php';
    require_once $rest;

    $files = glob($root . '/lib/*.php');
    foreach ($files as $file) {
        require_once $file;
    }

    require_once $root . '/Tests/SimpleMapprTest.php';
    require_once $root . '/Tests/SimpleMapprMixin.php';
    require_once $root . '/vendor/autoload.php';
}

function flushCaches()
{
    \SimpleMappr\Header::flush_cache(false);
    $dirItr = new RecursiveDirectoryIterator(dirname(__DIR__) . '/public/tmp');
    foreach (new RecursiveIteratorIterator($dirItr, RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
        if ($file->isFile() && $file->getFilename()[0] !== ".") {
            @unlink($file->getPathname());
        }
    }
}

/**
 * Loader function executed before all tests
 *
 * @return void
 */
function loader()
{
    switchConf();
    requireFiles();
    flushCaches();
    ob_start();
    file_get_contents("http://".MAPPR_DOMAIN);
    new \SimpleMappr\Header;
}

/**
 * Unloader function executed after all tests
 *
 * @return void
 */
function unloader()
{
    switchConf('restore');
    flushCaches();
    ob_end_clean();
}

spl_autoload_register(__NAMESPACE__.'\loader');
register_shutdown_function(__NAMESPACE__.'\unloader');