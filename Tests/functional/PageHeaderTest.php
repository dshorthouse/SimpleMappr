<?php

/**
 * Unit tests for Header class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class PageHeaderTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testPageTitle() {
    parent::setUpPage();
    $title = $this->webDriver->getTitle();
    $this->assertEquals('SimpleMappr', $title);
  }

  public function testLanguageEnglish() {
    parent::setUpPage();
    $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
    $this->assertEquals('en', $lang);
  }

  public function testLanguageFrancais() {
    $this->webDriver->get($this->url . "/?locale=fr_FR");
    $this->waitOnSpinner();
    $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
    $this->assertEquals('fr', $lang);
  }

  public function testDescription() {
    parent::setUpPage();
    $description = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='description']"))->getAttribute('content');
    $this->assertContains("Create free point maps for publications and presentations", $description);
  }

  public function testKeywords() {
    parent::setUpPage();
    $keywords = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='keywords']"))->getAttribute('content');
    $this->assertContains("publication,presentation,map,georeference", $keywords);
  }

  public function testAuthor() {
    parent::setUpPage();
    $this->assertContains("David P. Shorthouse", $this->metaElementContent("//meta[@name='author']"));
  }

  public function testOpenGraph() {
    parent::setUpPage();
    $og_title = $this->metaElementContent("//meta[@property='og:title']");
    $og_description = $this->metaElementContent("//meta[@property='og:description']");
    $og_locale = $this->metaElementContent("//meta[@property='og:locale']");
    $og_type = $this->metaElementContent("//meta[@property='og:type']");
    $og_url = $this->metaElementContent("//meta[@property='og:url']");
    $og_image = $this->metaElementContent("//meta[@property='og:image']");

    $this->assertContains("SimpleMappr", $og_title);
    $this->assertContains("Create free point maps for publications and presentations", $og_description);
    $this->assertContains("en_US", $og_locale);
    $this->assertContains("website", $og_type);
    $this->assertContains("http://" . MAPPR_DOMAIN, $og_url);
    $this->assertContains("http://" . MAPPR_DOMAIN . "/public/images/logo_og.png", $og_image);
  }

  private function metaElementContent($xpath) {
    return $this->webDriver->findElement(WebDriverBy::xpath($xpath))->getAttribute('content');
  }
}

?>