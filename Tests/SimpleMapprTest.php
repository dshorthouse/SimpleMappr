<?php

/**
 * Set-up of database & switching config files for use in tests
 */

abstract class SimpleMapprTest extends PHPUnit_Extensions_Selenium2TestCase {

  private static $db;

  public static function setUpBeforeClass() {

    self::$db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

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
      `identifier` varchar(255) NOT NULL,
      `username` varchar(50) DEFAULT NULL,
      `givenname` varchar(50) DEFAULT NULL,
      `surname` varchar(100) DEFAULT NULL,
      `email` varchar(50) DEFAULT NULL,
      `role` int(11) DEFAULT 1,
      `created` int(11) DEFAULT NULL,
      `access` int(11) DEFAULT NULL,
      PRIMARY KEY (`uid`),
      KEY `identifier` (`identifier`),
      KEY `idx_username` (`username`),
      KEY `idx_access` (`access`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

    $citations_table = 'CREATE TABLE IF NOT EXISTS `citations` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `year` int(11) NOT NULL,
      `reference` text COLLATE utf8_unicode_ci NOT NULL,
      `doi` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `first_author_surname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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

    self::$db->query($maps_table);
    self::$db->query($users_table);
    self::$db->query($citations_table);
    self::$db->query($stateprovinces_table);

    $user1 = self::$db->query_insert('users', array(
      'uid' => 1,
      'identifier' => 'administrator',
      'username' => 'administrator',
      'givenname' => 'Joe',
      'surname' => 'Smith',
      'email' => 'nowhere@example.com',
      'role' => 2
    ));

    $user2 = self::$db->query_insert('users', array(
      'uid' => 2,
      'identifier' => 'user',
      'username' => 'user',
      'givenname' => 'Jack',
      'surname' => 'Johnson',
      'email' => 'nowhere@example.com',
      'role' => 1
    ));

    self::$db->query_insert('maps', array(
      'uid' => $user1,
      'title' => 'Sample Map',
      'map' => '{}'
    ));
  }

  public static function tearDownAfterClass() {
    self::$db->query("DROP TABLE maps");
    self::$db->query("DROP TABLE users");
    self::$db->query("DROP TABLE citations");
    self::$db->query("DROP TABLE stateprovinces");
    self::$db = NULL;
  }

  public function setUp() {
    $this->setBrowser('firefox');
    $this->setBrowserUrl("http://" . MAPPR_DOMAIN . "/");
  }

  public function tearDown() {
    $root = dirname(dirname(__FILE__));
    $tmpfiles = glob($root."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
    foreach ($tmpfiles as $file) {
      unlink($file);
    }
    Header::flush_cache(false);
  }

}
?>