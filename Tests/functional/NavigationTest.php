<?php

/**
 * Unit tests for navigation/routes
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class NavigationTest extends SimpleMapprTest
{
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
        $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
        $this->assertEquals('cartes de points gratuits pour publications et présentations', $tagline->getText());
    }

    /**
     * Test presence of Sign In page.
     */
    public function testSignInPage()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Sign In'));
        $link->click();
        parent::waitOnSpinner();
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
        parent::waitOnSpinner();
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
        parent::waitOnSpinner();
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
        parent::waitOnSpinner();
        $content = $this->webDriver->findElement(WebDriverBy::id('map-help'));
        $this->assertContains('This application makes heavy use of JavaScript.', $content->getText());
    }

    /**
     * Test presence of user pages.
     */
    public function testUserPage()
    {
        parent::setUpPage();
        parent::setSession('user', 'fr_FR');
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'Jack Johnson');
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-session'))->getText(), 'Déconnecter');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Mes cartes'));
        $link->click();
        parent::waitOnSpinner();
        $content = $this->webDriver->findElement(WebDriverBy::id('map-mymaps'));
        $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->getText());
        $this->assertCount(0, $this->webDriver->findElements(WebDriverBy::linkText('Administration')));
    }

    /**
     * Test presence of admin pages.
     */
    public function testAdminPage()
    {
        parent::setUpPage();
        parent::setSession('administrator');
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
        $link->click();
        parent::waitOnSpinner();
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'John Smith');

        $matcher = array(
            'tag' => 'tbody',
            'parent' => array('attributes' => array('class' => 'grid-users')),
            'ancestor' => array('id' => 'userdata'),
            'children' => array('count' => 1)
        );
        $this->assertTag($matcher, $this->webDriver->getPageSource());

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        parent::waitOnSpinner();
        $matcher = array(
          'tag' => 'textarea',
          'id' => 'citation-reference',
          'ancestor' => array('id' => 'map-admin')
        );
        $this->assertTag($matcher, $this->webDriver->getPageSource());
    }

    /**
     * Test flushing of caches.
     */
    public function testFlushCache()
    {
        parent::setUpPage();
        parent::setSession('administrator');
        $orig_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
        $this->webDriver->findElement(WebDriverBy::linkText('Administration'))->click();
        parent::waitOnSpinner();
        $this->webDriver->findElement(WebDriverBy::linkText('Flush caches'))->click();
        if (!getenv('CI')) {
            $this->webDriver->wait()->until(WebDriverExpectedCondition::alertIsPresent());
            $dialog = $this->webDriver->switchTo()->alert();
            $this->assertEquals('Caches flushed', $dialog->getText());
            $dialog->accept();
            $this->webDriver->wait()->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::alertIsPresent()));
            sleep(2);
        }
        $this->webDriver->navigate()->refresh();
        $new_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
        $this->assertNotEquals($orig_css, $new_css);
    }

}