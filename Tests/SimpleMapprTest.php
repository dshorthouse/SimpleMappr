<?php

/**
 * Set-up of database & switching config files for use in tests
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

use PHPUnit\Framework\TestCase;
use SimpleMappr\Database;
use SimpleMappr\Header;

abstract class SimpleMapprTest extends TestCase
{

    protected static $db;
    protected $webDriver;
    protected $url;

    /**
     * Execute once before all tests
     */
    public static function setUpBeforeClass()
    {

        self::$db = Database::getInstance();
        self::dropTables();

        $maps_table = 'CREATE TABLE IF NOT EXISTS `maps` (
          `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `uid` int(11) NOT NULL,
          `title` varchar(255) CHARACTER SET latin1 NOT NULL,
          `map` longtext CHARACTER SET utf8 COLLATE utf8_bin,
          `created` int(11) NOT NULL,
          `updated` int(11) DEFAULT NULL,
          PRIMARY KEY (`mid`),
          KEY `uid` (`uid`),
          KEY `title` (`title`),
          KEY `idx_created` (`created`),
          KEY `idx_updated` (`updated`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

        $users_table = 'CREATE TABLE IF NOT EXISTS `users` (
          `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `hash` varchar(60) NOT NULL,
          `identifier` varchar(255) NOT NULL,
          `username` varchar(50) DEFAULT NULL,
          `displayname` varchar(125) DEFAULT NULL,
          `email` varchar(50) DEFAULT NULL,
          `role` int(11) DEFAULT 1,
          `created` int(11) DEFAULT NULL,
          `access` int(11) DEFAULT NULL,
          PRIMARY KEY (`uid`),
          UNIQUE KEY `idx_users_hash` (`hash`),
          KEY `identifier` (`identifier`),
          KEY `idx_username` (`username`),
          KEY `idx_access` (`access`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

        $citations_table = 'CREATE TABLE IF NOT EXISTS `citations` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `year` int(11) NOT NULL,
          `reference` text COLLATE utf8_unicode_ci DEFAULT NULL,
          `doi` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `first_author_surname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `created` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `year` (`year`,`first_author_surname`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

        $stateprovinces_table = 'CREATE TABLE IF NOT EXISTS `stateprovinces` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `country_iso` char(3) DEFAULT NULL,
          `country` varchar(128) DEFAULT NULL,
          `stateprovince` varchar(128) DEFAULT NULL,
          `stateprovince_code` char(2) NOT NULL,
          UNIQUE KEY `OBJECTID` (`id`),
          KEY `index_on_country` (`country`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

        $shares_table = 'CREATE TABLE IF NOT EXISTS `shares` (
          `sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `mid` int(11) NOT NULL,
          `created` int(11) NOT NULL,
          PRIMARY KEY (`sid`),
          KEY `index_on_mid` (`mid`),
          KEY `idx_created` (`created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
    
        self::$db->exec($maps_table);
        self::$db->exec($users_table);
        self::$db->exec($citations_table);
        self::$db->exec($stateprovinces_table);
        self::$db->exec($shares_table);

        $user1 = self::$db->queryInsert('users', [
          'uid' => 1,
          'hash' => password_hash('administrator', PASSWORD_DEFAULT),
          'identifier' => 'administrator',
          'username' => 'administrator',
          'displayname' => 'John Smith',
          'email' => 'nowhere@example.com',
          'role' => 2
        ]);

        $user2 = self::$db->queryInsert('users', [
          'uid' => 2,
          'hash' => password_hash('user', PASSWORD_DEFAULT),
          'identifier' => 'user',
          'username' => 'user',
          'displayname' => 'Jack Johnson',
          'email' => 'nowhere@example.com',
          'role' => 1
        ]);

        $map_data1 = [
          'coords' => 
            [
            0 => 
            [
              'title' => 'Sample Data',
              'data' => '55, -115',
              'shape' => 'star',
              'size' => '14',
              'color' => '255 32 3',
            ],
            1 => 
            [
              'title' => '',
              'data' => '',
              'shape' => 'circle',
              'size' => '10',
              'color' => '0 0 0',
            ],
            2 => 
            [
              'title' => '',
              'data' => '',
              'shape' => 'circle',
              'size' => '10',
              'color' => '0 0 0',
            ],
          ],
          'regions' => 
          [
            0 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
            1 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
            2 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
          ],
          'layers' => 
          [
            'countries' => 'on',
            'stateprovinces' => 'on',
          ],
          'gridspace' => '',
          'projection' => 'epsg:4326',
          'origin' => '',
          'filter-mymap' => '',
          'citation' => 
          [
            'reference' => '',
            'first_author_surname' => '',
            'year' => '',
            'doi' => '',
            'link' => '',
          ],
          'download-filetype' => 'svg',
          'download-factor' => '1',
          'download' => '',
          'output' => 'png',
          'download_token' => '1398911053520',
          'bbox_map' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'projection_map' => 'epsg:4326',
          'bbox_rubberband' => '',
          'bbox_query' => '',
          'pan' => '',
          'zoom_out' => '',
          'crop' => '',
          'rotation' => '0',
          'save' => 
          [
            'title' => 'Sample Map Administrator',
          ],
          'file_name' => '',
          'download_factor' => '1',
          'width' => '',
          'height' => '',
          'download_filetype' => 'svg',
          'grid_space' => '',
          'options' => 
          [
            'border' => '',
            'legend' => '',
            'scalebar' => '',
            'scalelinethickness' => '',
          ],
          'border_thickness' => '',
          'rendered_bbox' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'rendered_rotation' => '0',
          'rendered_projection' => 'epsg:4326',
          'bad_points' => '',
          'bad_drawings' => ''
        ];

        $map_data2 = [
          'coords' => 
          [
            0 => 
            [
              'title' => 'More Sample Data',
              'data' => '45, -115',
              'shape' => 'circle',
              'size' => '14',
              'color' => '255 32 3',
            ],
            1 => 
            [
              'title' => '',
              'data' => '',
              'shape' => 'circle',
              'size' => '10',
              'color' => '0 0 0',
            ],
            2 => 
            [
              'title' => '',
              'data' => '',
              'shape' => 'circle',
              'size' => '10',
              'color' => '0 0 0',
            ],
          ],
          'regions' => 
          [
            0 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
            1 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
            2 => 
            [
              'title' => '',
              'data' => '',
              'color' => '150 150 150',
            ],
          ],
          'layers' => 
          [
            'countries' => 'on',
            'stateprovinces' => 'on',
          ],
          'gridspace' => '',
          'projection' => 'epsg:4326',
          'origin' => '',
          'filter-mymap' => '',
          'citation' => 
          [
            'reference' => '',
            'first_author_surname' => '',
            'year' => '',
            'doi' => '',
            'link' => '',
          ],
          'download-filetype' => 'svg',
          'download-factor' => '1',
          'download' => '',
          'output' => 'png',
          'download_token' => '1398911053520',
          'bbox_map' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'projection_map' => 'epsg:4326',
          'bbox_rubberband' => '',
          'bbox_query' => '',
          'pan' => '',
          'zoom_out' => '',
          'crop' => '',
          'rotation' => '0',
          'save' => 
          [
            'title' => 'Sample Map User',
          ],
          'file_name' => '',
          'download_factor' => '1',
          'width' => '',
          'height' => '',
          'download_filetype' => 'svg',
          'grid_space' => '',
          'options' => 
          [
            'border' => '',
            'legend' => '',
            'scalebar' => '',
            'scalelinethickness' => '',
          ],
          'border_thickness' => '',
          'rendered_bbox' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'rendered_rotation' => '0',
          'rendered_projection' => 'epsg:4326',
          'bad_points' => '',
          'bad_drawings' => ''
        ];

        $map_data3 = [
          'coords' => [
              0 => 
              [
                'title' => '',
                'data' => '',
                'shape' => 'circle',
                'size' => '10',
                'color' => '0 0 0',
              ],
              1 => 
              [
                'title' => '',
                'data' => '',
                'shape' => 'circle',
                'size' => '10',
                'color' => '0 0 0',
              ],
              2 => 
              [
                'title' => '',
                'data' => '',
                'shape' => 'circle',
                'size' => '10',
                'color' => '0 0 0',
              ],
          ],
          'regions' => [
              0 => 
              [
                'title' => '',
                'data' => '',
                'color' => '150 150 150',
              ],
              1 => 
              [
                'title' => '',
                'data' => '',
                'color' => '150 150 150',
              ],
              2 => 
              [
                'title' => '',
                'data' => '',
                'color' => '150 150 150',
              ],
          ],
          'wkt' => [
              0 => 
              [
                'data' => 'POLYGON((-70 63,-70 48,-106 48,-106 63,-70 63))',
                'color' => '255 0 0',
                'title' => 'My Polygon'
              ],
              1 => 
              [
                'data' => '',
                'title' => '',
                'color' => '150 150 150'
              ],
              2 => 
              [
                'data' => '',
                'title' => '',
                'color' => '150 150 150'
              ]
          ],
          'layers' => 
          [
            'countries' => 'on',
            'stateprovinces' => 'on',
          ],
          'gridspace' => '',
          'projection' => 'epsg:4326',
          'origin' => '',
          'filter-mymap' => '',
          'citation' => [],
          'download-filetype' => 'svg',
          'download-factor' => '1',
          'download' => '',
          'output' => 'png',
          'download_token' => '1398911053520',
          'bbox_map' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'projection_map' => 'epsg:4326',
          'bbox_rubberband' => '',
          'bbox_query' => '',
          'pan' => '',
          'zoom_out' => '',
          'crop' => '',
          'rotation' => '0',
          'save' => 
          [
            'title' => 'Second Sample Map User',
          ],
          'file_name' => '',
          'download_factor' => '1',
          'width' => '',
          'height' => '',
          'download_filetype' => 'svg',
          'grid_space' => '',
          'options' => 
          [
            'border' => '',
            'legend' => '',
            'scalebar' => '',
            'scalelinethickness' => '',
          ],
          'border_thickness' => '',
          'rendered_bbox' => '-161.8472160357,18.5000000000,-72.1478841870,63.5000000000',
          'rendered_rotation' => '0',
          'rendered_projection' => 'epsg:4326',
          'bad_points' => '',
          'bad_drawings' => ''
        ];

        $map1 = self::$db->queryInsert('maps', [
          'mid' => 1,
          'uid' => $user1,
          'title' => 'Sample Map Administrator',
          'map' => json_encode($map_data1),
          'created' => time()-(7 * 24 * 60 * 60)
        ]);

        self::$db->queryInsert('maps', [
          'mid' => 2,
          'uid' => $user2,
          'title' => 'Sample Map User',
          'map' => json_encode($map_data2),
          'created' => time()
        ]);

        self::$db->queryInsert('maps', [
          'mid' => 3,
          'uid' => $user2,
          'title' => 'Second Sample Map User',
          'map' => json_encode($map_data3),
          'created' => time()
        ]);

        self::$db->queryInsert('shares', [
            'mid' => $map1,
            'created' => time()
        ]);

        self::$db->queryInsert('citations', [
          'year' => 2010,
          'reference' => 'Shorthouse, David P. 2010. SimpleMappr, an online tool to produce publication-quality point maps. [Retrieved from http://www.simplemappr.net. Accessed 02 December, 2013].',
          'doi' => '10.XXXX/XXXXXX',
          'first_author_surname' => 'Shorthouse',
          'created' => time()
        ]);

        self::$db->queryInsert('stateprovinces', [
          'country' => 'Canada',
          'country_iso' => 'CAN',
          'stateprovince' => 'Alberta',
          'stateprovince_code' => 'AB'
        ]);

          session_cache_limiter('nocache');
          session_start();
    }

    /**
     * Execute once after all tests.
     */
    public static function tearDownAfterClass()
    {
        self::dropTables();
        self::$db = null;

        session_write_close();
    }

    /**
     * Drop all tables.
     */
    public static function dropTables()
    {
        self::$db->exec("DROP TABLE IF EXISTS maps");
        self::$db->exec("DROP TABLE IF EXISTS users");
        self::$db->exec("DROP TABLE IF EXISTS citations");
        self::$db->exec("DROP TABLE IF EXISTS shares");
        self::$db->exec("DROP TABLE IF EXISTS stateprovinces");
    }

    /**
     * Check if two files are identical.
     *
     * @param string $fn1 First file directory.
     * @param string $fn2 Second file directory.
     * @return bool
     */
    public static function filesIdentical($fn1, $fn2)
    {
        if (filetype($fn1) !== filetype($fn2)) {
            return false;
        }
        if (filesize($fn1) !== filesize($fn2)) {
            return false;
        }
        if (!$fp1 = fopen($fn1, 'rb')) {
            return false;
        }

        if (!$fp2 = fopen($fn2, 'rb')) {
            fclose($fp1);
            return false;
        }

        $same = true;
        while (!feof($fp1) and !feof($fp2)) {
            if (fread($fp1, 4096) !== fread($fp2, 4096)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

    /**
     * Check if two images are very similar.
     *
     * @param string $fn1 First image directory.
     * @param string $fn2 Second image directory.
     * @return bool
     */
    public static function imagesSimilar($fn1, $fn2)
    {
        $same = false;

        $image1 = new \Imagick($fn1);
        $image2 = new \Imagick($fn2);
        $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);
        if ($result[1] < 0.01) {
            $same = true;
        }
        return $same;
    }

    /**
     * Parent setUp function executed before each test.
     */
    public function setUp()
    {
        $host = 'http://localhost:4444/wd/hub';
        $browser = BROWSER;
        $capabilities = DesiredCapabilities::$browser();
        $capabilities->setCapability(WebDriverCapabilityType::JAVASCRIPT_ENABLED, true);
        $capabilities->setCapability(WebDriverCapabilityType::HANDLES_ALERTS, true);
        if (BROWSER == "firefox") {
            $capabilities->setCapability("marionette", true);
            $capabilities->setCapability("webdriver.gecko.driver", "/usr/local/bin/geckodriver");
        }
        $this->webDriver = RemoteWebDriver::create($host, $capabilities);
        $this->webDriver->manage()->window()->setSize(new WebDriverDimension(1280, 1024));
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        if(method_exists($this->webDriver, 'quit')) {
            $this->webDriver->close();
            $this->webDriver->quit();
        }
    }

    /**
     * Get a URL
     */
    public function setUpPage()
    {
        new Header;
        $this->webDriver->get(MAPPR_URL);
        $this->waitOnSpinner();
    }

    /**
     * Wait on jQuery ajax then fall back to a sleep.
     */
    public function waitOnAjax($timeout = 10, $interval = 200)
    {
        $this->webDriver->wait($timeout, $interval)->until(function() {
            $condition = 'return ($.active == 0);';
            return $this->webDriver->executeScript($condition);
        });
    }

    /**
     * Wait on spinner then fall back to a sleep.
     */
    public function waitOnSpinner($timeout = 10, $interval = 200)
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
    public function waitOnMap($timeout = 10, $interval = 200)
    {
        $this->webDriver->wait($timeout, $interval)->until(function() {
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
        $db = Database::getInstance();
        $sql = "SELECT * from users u WHERE u.username=:username";
        $db->prepare($sql);
        $db->bindParam(":username", $username, 'string');
        $user = $db->fetchFirstArray();
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