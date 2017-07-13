<?php

/**
 * Test Bootstrapper for executing PHP Unit tests
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
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
        $this->requireFiles();
        $this->flushCaches();
        ob_start();
        new \SimpleMappr\Assets;
    }

    /**
     * Unloader function executed after all tests
     *
     * @return void
     */
    private function unloader()
    {
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
     * Require all files necessary to execute tests
     *
     * @return void
     */
    private function requireFiles()
    {
        require_once $this->root_dir . '/config/conf.test.php';
        require_once $this->root_dir . '/Tests/SimpleMapprTestCase.php';
        require_once $this->root_dir . '/Tests/SimpleMapprFunctionalTestCase.php';
        require_once $this->root_dir . '/vendor/autoload.php';
    }

    /**
     * Flush all the caches and clear the tmp files
     *
     * @return void
     */
    private function flushCaches()
    {
        \SimpleMappr\Assets::flushCache(false);
        $this->clearTmpFiles();
    }
}

$autoloader = new SimpleMapprTestBootstrap(dirname(__DIR__));
