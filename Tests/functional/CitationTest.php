<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
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
        parent::setUpPage();
        parent::setSession('administrator');

        $ch = curl_init(MAPPR_URL . "/citation.json");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $this->assertEquals('application/json; charset=UTF-8', $type);
        $this->assertCount(1, $result->citations);
    }

    /**
     * Test addition of a citation.
     */
    public function testAddCitation()
    {
        parent::setUpPage();
        parent::setSession('administrator');

        $citation = 'Shorthouse, David P. 2003. Another citation';
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $this->webDriver->findElement(WebDriverBy::id('citation-reference'))->sendKeys($citation);
        $this->webDriver->findElement(WebDriverBy::id('citation-surname'))->sendKeys('Shorthouse');
        $this->webDriver->findElement(WebDriverBy::id('citation-year'))->sendKeys('2003');
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Add citation']"))->click();
        parent::waitOnAjax();
        $citation_list = $this->webDriver->findElement(WebDriverBy::id('admin-citations-list'))->getText();
        $this->assertContains($citation, $citation_list);
        parent::$db->exec("DELETE FROM citations WHERE reference = '".$citation."'");
    }

    /**
     * Test deletion of a citation.
     */
    public function testDeleteCitation()
    {
        $citation_id = parent::$db->queryInsert("citations", array(
            'year' => 2015,
            'reference' => 'Aaarnoldson, Peter. 2015. Here be a new citation. [Retrieved from http://www.simplemappr.net. Accessed 01 January, 2015].',
            'doi' => '10.XXXX/XXXXXX',
            'first_author_surname' => 'Aaarnoldson'
        ));

        parent::setUpPage();
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $delete_link = $this->webDriver->findElements(WebDriverBy::cssSelector('#admin-citations-list > .citation > .citation-delete'))[0];
        $delete_link->click();
        $this->webDriver->findElement(WebDriverBy::cssSelector('.ui-dialog-buttonset > .negative'))->click();
        parent::waitOnAjax();
        $citations_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#admin-citations-list > .citation'));
        $result = parent::$db->query("SELECT COUNT(*) as cnt FROM citations");
        $this->assertEquals($result[0]->cnt, count($citations_list));
        parent::$db->exec("DELETE FROM citations WHERE id = ".$citation_id."");
    }

}