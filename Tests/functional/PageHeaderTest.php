<?php

/**
 * Unit tests for Header class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class PageHeaderTest extends SimpleMapprTest
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
     * test page title.
     */
    public function testPageTitle()
    {
        parent::setUpPage();
        $title = $this->webDriver->getTitle();
        $this->assertEquals('SimpleMappr', $title);
    }

    /**
     * Test default language attribute to HTML tag.
     */
    public function testLanguageEnglish()
    {
        parent::setUpPage();

        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('en', $lang);
    }

    /**
     * Test lang=fr attribute for HTML tag.
     */
    public function testLanguageFrancais()
    {
        $this->webDriver->get($this->url . "/?locale=fr_FR");
        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('fr', $lang);
    }

    /**
     * Test meta description.
     */
    public function testDescription()
    {
        parent::setUpPage();

        $description = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='description']"))->getAttribute('content');
        $this->assertContains("Create free point maps for publications and presentations", $description);
    }

    /**
     * Test meta keywords.
     */
    public function testKeywords()
    {
        parent::setUpPage();

        $keywords = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='keywords']"))->getAttribute('content');
        $this->assertContains("publication,presentation,map,georeference", $keywords);
    }

    /**
     * Test meta author.
     */
    public function testAuthor()
    {
        parent::setUpPage();

        $this->assertContains("David P. Shorthouse", $this->metaElementContent("//meta[@name='author']"));
    }

    /**
     * Test OpenGraph content.
     */
    public function testOpenGraph()
    {
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

    /**
     * Test Twitter card
     */
    public function testTwitterCard()
    {
        parent::setUpPage();

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