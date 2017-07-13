<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */
class CitationControllerTest extends SimpleMapprTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test addition of a citation.
     */
    public function testAddCitation()
    {
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
     * Test response from index is JSON for single citation.
     */
    public function testEditCitation()
    {
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $edit_link = $this->webDriver->findElements(WebDriverBy::cssSelector('#admin-citations-list > .citation > .citation-update'))[0];
        $edit_link->click();
        $surname = $this->webDriver->findElement(WebDriverBy::id('citation-surname'))->getAttribute('value');
        $year = $this->webDriver->findElement(WebDriverBy::id('citation-year'))->getAttribute('value');
        $doi = $this->webDriver->findElement(WebDriverBy::id('citation-doi'))->getAttribute('value');
        $reference = $this->webDriver->findElement(WebDriverBy::id('citation-reference'))->getAttribute('value');
        $this->assertEquals(parent::stubbedCitation()["first_author_surname"], $surname);
        $this->assertEquals(parent::stubbedCitation()["year"], $year);
        $this->assertEquals(parent::stubbedCitation()["doi"], $doi);
        $this->assertEquals(parent::stubbedCitation()["reference"], $reference);
    }

    /**
     * Test deletion of a citation.
     */
    public function testDeleteCitation()
    {
        $citation_id = parent::$db->queryInsert("citations", [
            'year' => 2015,
            'reference' => 'Aaarnoldson, Peter. 2015. Here be a new citation. [Retrieved from http://www.simplemappr.net. Accessed 01 January, 2015].',
            'doi' => '10.XXXX/XXXXXX',
            'first_author_surname' => 'Aaarnoldson',
            'created' => time()
        ]);

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
