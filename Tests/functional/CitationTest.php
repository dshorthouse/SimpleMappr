<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class CitationTest extends SimpleMapprTest
{
    /**
     * Parent setUp function executed before each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test response from index is JSON with one record.
     */
    public function testCitationsIndex()
    {
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

    /**
     * Test addition of a citation.
     */
    public function testAddCitation()
    {
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

    /**
     * Test deletion of a citation.
     */
    public function testDeleteCitation()
    {
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
        $result = parent::$db->query("SELECT COUNT(*) as cnt FROM citations");
        $this->assertEquals($result[0]->cnt, count($citations_list));
    }

}