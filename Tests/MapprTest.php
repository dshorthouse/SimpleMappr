<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class MapprTest extends PHPUnit_Extensions_Selenium2TestCase {

  protected $app_url;

  public function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/";
    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->app_url);
  }

}

?>