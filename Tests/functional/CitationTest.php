<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class CitationTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }
  
  public function tearDown() {
    parent::tearDown();
  }

  public function testCitationsIndex() {
    $ch = curl_init($this->url . "/citation");

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = json_decode(curl_exec($ch));
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    $this->assertEquals('application/json', $type);
    $this->assertCount(1, $result->citations);
  }

  public function testAddCitation() {
    $citation = 'Shorthouse, David P. 2003. Another citation';
    parent::setUpPage();
    parent::setSession('administrator');
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
    $link->click();
    parent::waitOnSpinner();
    $this->webDriver->findElement(WebDriverBy::id('citation-reference'))->sendKeys($citation);
    $this->webDriver->findElement(WebDriverBy::id('citation-surname'))->sendKeys('Shorthouse');
    $this->webDriver->findElement(WebDriverBy::id('citation-year'))->sendKeys('2003');
    $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add citation']"))->click();
    parent::waitOnSpinner();
    $citation_list = $this->webDriver->findElement(WebDriverBy::id('admin-citations-list'))->getText();
    $this->assertContains($citation, $citation_list);
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Sign Out'));
    $link->click();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('About'));
    $link->click();
    parent::waitOnSpinner();
    $about_page = $this->webDriver->findElement(WebDriverBy::id('map-about'))->getText();
    $this->assertContains($citation, $about_page);
  }

  public function testDeleteCitation() {
    parent::setUpPage();
    parent::setSession('administrator');
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
    $link->click();
    parent::waitOnSpinner();
    $citation = $this->webDriver->findElements(WebDriverBy::cssSelector('#admin-citations-list > .citation > .citation-delete'))[0];
    $citation->click();
    $this->webDriver->findElement(WebDriverBy::cssSelector('.ui-dialog-buttonset > .negative'))->click();
    parent::waitOnSpinner();
    $citations_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#admin-citations-list > .citation'));
    $citation_count = parent::$db->query_first("SELECT COUNT(*) as cnt FROM citations")['cnt'];
    $this->assertEquals($citation_count, count($citations_list));
  }

}