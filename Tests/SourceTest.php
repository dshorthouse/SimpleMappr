<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class SourceTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testTitle() {
    parent::setUpPage();
    $this->assertEquals('SimpleMappr', $this->webDriver->getTitle());
  }

  public function testDescription() {
    parent::setUpPage();
    $this->assertContains('meta content="A point map web application for quality publications and presentations." name="description"', $this->webDriver->getPageSource());
  }

  public function testKeywords() {
    parent::setUpPage();
    $this->assertContains('meta content="publication,presentation,map,georeference" name="keywords"', $this->webDriver->getPageSource());
  }

  public function testAuthor() {
    parent::setUpPage();
    $this->assertContains('meta content="David P. Shorthouse" name="author"', $this->webDriver->getPageSource());
  }

  public function testOpenGraph() {
    parent::setUpPage();
    $this->assertContains('html xmlns="http://www.w3.org/1999/xhtml" lang="en" prefix="og: http://ogp.me/ns#"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="SimpleMappr" property="og:title"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="A point map web application for quality publications and presentations." property="og:description"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="en_US" property="og:locale"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="website" property="og:type"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="http://' . MAPPR_DOMAIN . '" property="og:url"', $this->webDriver->getPageSource());
    $this->assertContains('meta content="http://' . MAPPR_DOMAIN . '/public/images/logo_og.png" property="og:image"', $this->webDriver->getPageSource());
  }
}

?>