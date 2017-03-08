<?php

/**
 * Unit tests for navigation/routes
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class NavigationTest extends SimpleMapprTest
{
    use SimpleMapprMixin;

    /**
     * Parent setUp function executed before each test
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Parent tearDown function executed after each test
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test presence of tag line.
     */
    public function testTagline()
    {
        parent::setUpPage();

        $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
        $this->assertEquals('create free point maps for publications and presentations', $tagline->getText());
    }

    /**
     * Test translated tag line.
     */
    public function testTaglineFrench()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Français'));
        $link->click();
        parent::waitOnAjax();
        $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
        $this->assertEquals('créez gratuitement des cartes de répartition pour publications et présentations', $tagline->getText());
    }

    /**
     * Test presence of Sign In page.
     */
    public function testSignInPage()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Sign In'));
        $link->click();
        parent::waitOnAjax();
        $tagline = $this->webDriver->findElement(WebDriverBy::id('map-mymaps'));
        $this->assertContains('Save and reload your map data or create a generic template.', $tagline->getText());
    }

    /**
     * Test presence of API page.
     */
    public function testAPIPage()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('API'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('general-api'));
        $this->assertContains('A simple, restful API may be used with Internet accessible', $content->getText());
    }

    /**
     * Test presence of About page.
     */
    public function testAboutPage()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('About'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('general-about'));
        $this->assertContains('Create free point maps suitable for reproduction on print media', $content->getText());
    }

    /**
     * Test presence of Help page.
     */
    public function testHelpPage()
    {
        parent::setUpPage();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Help'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('map-help'));
        $this->assertContains('This application makes heavy use of JavaScript.', $content->getText());
    }

    /**
     * Test 404 page
     */
    public function test404Page()
    {
        $this->webDriver->get(MAPPR_URL . "/doesnotexist");
        $content = $this->webDriver->findElement(WebDriverBy::id('error-message'));
        $this->assertContains('The page you requested was not found', $content->getText());
    }

}