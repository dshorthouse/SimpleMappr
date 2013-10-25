<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class SourceTest extends PHPUnit_Extensions_Selenium2TestCase {

  protected $app_url;

  public function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/";
    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->app_url);
  }
  
  public function setUpPage() {
    $this->url($this->app_url);
  }

  public function testTitle() {
    $this->assertEquals('SimpleMappr', $this->title());
  }

  public function testDescription() {
    $this->assertContains('meta content="A point map web application for quality publications and presentations." name="description"', $this->source());
  }

  public function testKeywords() {
    $this->assertContains('meta content="publication,presentation,map,georeference" name="keywords"', $this->source());
  }

  public function testAuthor() {
    $this->assertContains('meta content="David P. Shorthouse" name="author"', $this->source());
  }

  public function testOpenGraph() {
    $this->assertContains('html xmlns="http://www.w3.org/1999/xhtml" lang="en" prefix="og: http://ogp.me/ns#"', $this->source());
    $this->assertContains('meta content="SimpleMappr" property="og:title"', $this->source());
    $this->assertContains('meta content="A point map web application for quality publications and presentations." property="og:description"', $this->source());
    $this->assertContains('meta content="en_US" property="og:locale"', $this->source());
    $this->assertContains('meta content="website" property="og:type"', $this->source());
    $this->assertContains('meta content="http://' . MAPPR_DOMAIN . '" property="og:url"', $this->source());
    $this->assertContains('meta content="http://' . MAPPR_DOMAIN . '/public/images/logo_og.png" property="og:image"', $this->source());
  }
}

?>