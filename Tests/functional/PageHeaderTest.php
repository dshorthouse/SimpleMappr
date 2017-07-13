<?php

/**
 * Unit tests for Header class
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
class PageHeaderTest extends SimpleMapprTestCase
{
    /**
     * test page title.
     */
    public function testPageTitle()
    {
        $title = $this->webDriver->getTitle();
        $this->assertEquals('SimpleMappr', $title);
    }

    /**
     * Test default language attribute to HTML tag.
     */
    public function testLanguageEnglish()
    {
        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('en', $lang);
    }

    /**
     * Test lang=fr attribute for HTML tag.
     */
    public function testLanguageFrancais()
    {
        $this->webDriver->get(MAPPR_URL . "/?locale=fr_FR");
        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('fr', $lang);
        $this->webDriver->get(MAPPR_URL . "/?locale=en_US");
    }

    /**
     * Test meta description.
     */
    public function testDescription()
    {
        $description = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='description']"))->getAttribute('content');
        $this->assertContains("Create free point maps for publications and presentations", $description);
    }

    /**
     * Test meta keywords.
     */
    public function testKeywords()
    {
        $keywords = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='keywords']"))->getAttribute('content');
        $this->assertContains("publication,presentation,map,georeference", $keywords);
    }

    /**
     * Test meta author.
     */
    public function testAuthor()
    {
        $this->assertContains("David P. Shorthouse", $this->metaElementContent("//meta[@name='author']"));
    }

    /**
     * Test OpenGraph content.
     */
    public function testOpenGraph()
    {
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
        $this->assertContains(MAPPR_URL, $og_url);
        $this->assertContains(MAPPR_URL . "/public/images/logo_og.png", $og_image);
    }

    /**
     * Test Twitter card
     */
    public function testTwitterCard()
    {
        $twitter_card = $this->metaElementContent("//meta[@name='twitter:card']");
        $twitter_site = $this->metaElementContent("//meta[@name='twitter:site']");
        $twitter_creator = $this->metaElementContent("//meta[@name='twitter:creator']");

        $this->assertContains("summary", $twitter_card);
        $this->assertContains("@SimpleMappr", $twitter_site);
        $this->assertContains("@dpsSpiders", $twitter_creator);
    }

    /**
     * Obtain content of an XPATH element.
     *
     * @param string $xpath The XPATH of interest.
     * @return string The content.
     */
    private function metaElementContent($xpath)
    {
        return $this->webDriver->findElement(WebDriverBy::xpath($xpath))->getAttribute('content');
    }
}
