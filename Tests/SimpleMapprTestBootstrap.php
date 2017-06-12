<?php

/**
 * Test Bootstrapper for executing PHP Unit tests
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

require_once dirname(__DIR__) . '/Tests/SimpleMapprTestMixin.php';

class SimpleMapprTestBootstrap
{
    use SimpleMapprTestMixin;

    private $root_dir;

    /**
     * Constructor
     *
     * @param string $dir
     */
    public function __construct($dir)
    {
        date_default_timezone_set("America/New_York");
        $this->root_dir = $dir;
        $this->loader();
    }

    public function __destruct()
    {
        $this->unloader();
    }

    /**
     * Loader function executed before all tests
     *
     * @return void
     */
    private function loader()
    {
        $this->switchXdebug();
        $this->switchConf();
        $this->requireFiles();
        $this->flushCaches();
        ob_start();
        new \SimpleMappr\Header;
    }

    /**
     * Unloader function executed after all tests
     *
     * @return void
     */
    private function unloader()
    {
        $this->switchConf('restore');
        $this->flushCaches();
        $this->switchXdebug('enable');
    }

    /**
     * Switch xdebug to speed up text execution
     *
     * @param string $dir Enable/disable flag
     * @return void
     */
    private function switchXdebug($dir = 'disable')
    {
        if (function_exists('xdebug_'.$dir)) { 
            call_user_func('xdebug_'.$dir); 
        }
    }

    /**
     * Switch configuration files
     *
     * @param bool $restore Flag to toggle swap and replacement of config files
     * @return void
     */
    private function switchConf($restore = false)
    {
        $config_dir = $this->root_dir . '/config/';

        $conf = [
            'prod' => $config_dir . 'conf.php',
            'test' => $config_dir . 'conf.test.php'
        ];

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
    private function requireFiles()
    {
        require_once $this->root_dir . '/config/conf.php';
        require_once $this->root_dir . '/Tests/SimpleMapprTestCase.php';
        require_once $this->root_dir . '/vendor/autoload.php';
    }

    /**
     * Flush all the caches and clear the tmp files
     *
     * @return void
     */
    private function flushCaches()
    {
        \SimpleMappr\Header::flushCache(false);
        $this->clearTmpFiles();
    }
}

$autoloader = new SimpleMapprTestBootstrap(dirname(__DIR__));