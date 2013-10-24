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

  public function testTitle() {
    $this->url($this->app_url);
    $this->assertEquals('SimpleMappr', $this->title());
  }
  
  public function testTranslation() {
    $this->url($this->app_url);
    $link = $this->byLinkText('Français');
    $link->click();
    $tagline = $this->byId('site-tagline');
    $this->assertEquals('cartes point pour la publication et présentation', $tagline->text());
  }

}

?>