<?php

/**
 * Set-up of database & switching config files for use in tests
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */

use \SimpleMappr\Assets;

abstract class SimpleMapprFunctionalTestCase extends SimpleMapprTestCase
{
    protected static $webDriverSession;
    protected $webDriver;
    protected $url;

    /**
     * Execute once before all tests
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::setUpWebDriver();
        session_cache_limiter('nocache');
        session_start();
    }

    /**
     * Execute once after all tests.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::destroyWebDriver();
        session_write_close();
    }

    public static function setUpWebDriver()
    {
        $host = 'http://localhost:4444/wd/hub';
        $browser = getenv('BROWSER');
        $capabilities = DesiredCapabilities::$browser();
        $capabilities->setCapability(WebDriverCapabilityType::JAVASCRIPT_ENABLED, true);
        $capabilities->setCapability(WebDriverCapabilityType::HANDLES_ALERTS, true);
        $capabilities->setCapability(WebDriverCapabilityType::WEB_STORAGE_ENABLED, true);
        $capabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED, false);
        $capabilities->setCapability(WebDriverCapabilityType::DATABASE_ENABLED, false);
        $capabilities->setCapability(WebDriverCapabilityType::BROWSER_CONNECTION_ENABLED, false);
        $webDriver = RemoteWebDriver::create($host, $capabilities, 60000, 60000);
        $webDriver->manage()->deleteAllCookies();
        $webDriver->manage()->window()->setSize(new WebDriverDimension(1280, 1024));
        new Assets;
        $webDriver->get(MAPPR_URL);
        self::$webDriverSession = $webDriver->getSessionID();
    }

    public static function destroyWebDriver()
    {
        foreach (RemoteWebDriver::getAllSessions() as $session) {
            RemoteWebDriver::createBySessionID($session["id"])->quit();
        }
    }

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $this->webDriver = RemoteWebDriver::createBySessionID(self::$webDriverSession);
        $this->webDriver->navigate()->refresh();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    protected function tearDown()
    {
        if ($this->webDriver) {
            $this->webDriver->manage()->deleteAllCookies();
            unset($_SESSION["simplemappr"]);
            if (session_id() !== "") {
                session_unset();
                session_destroy();
            }
        }
    }

    /**
     * Wait on jQuery ajax then fall back to a sleep.
     */
    public function waitOnAjax($timeout = 20, $interval = 1000)
    {
        $this->webDriver->wait($timeout, $interval)->until(function () {
            $condition = 'return ($.active == 0);';
            return $this->webDriver->executeScript($condition);
        });
    }

    /**
     * Wait on spinner then fall back to a sleep.
     */
    public function waitOnSpinner($timeout = 20, $interval = 1000)
    {
        $this->webDriver->wait($timeout, $interval)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::id('map-loader')
            )
        );
    }

    /**
     * Wait on spinner then fall back to a sleep.
     */
    public function waitOnMap($timeout = 20, $interval = 1000)
    {
        $this->webDriver->wait($timeout, $interval)->until(function () {
            $src = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
            return (strpos($src, MAPPR_MAPS_URL) !== false) ? true : false;
        });
    }

    /**
     * Set a user session, add a cookie, then refresh the page
     *
     * @param string $username User name (values are "user" or "administrator").
     * @param string $locale Set the locale for the user.
     * @return void
     */
    public function setSession($username = "user", $locale = 'en_US')
    {
        $user = parent::getUser($username);
        $user['locale'] = $locale;

        $clone = array_merge([], $user);
        unset($clone['uid'], $clone['role']);

        $cookie = [
            "name" => "simplemappr",
            "value" => urlencode(json_encode($clone)),
            "path" => "/"
        ];
        $this->webDriver->manage()->addCookie($cookie);
        $_SESSION["simplemappr"] = $clone;
        $this->webDriver->navigate()->refresh();
        $this->waitOnAjax();

        return $user;
    }
}
