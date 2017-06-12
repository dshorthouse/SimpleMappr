<?php

/**
 * Unit tests for navigation/routes
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */

class Administration extends SimpleMapprTest
{
    /**
     * Test count of users in Users table.
     */
    public function testUserCountTable()
    {
        parent::setUpPage();
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
        $link->click();
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'John Smith');
        $users = $this->webDriver->findElements(WebDriverBy::cssSelector('#userdata > .grid-users > tbody > tr'));
        $this->assertEquals(count($users), 2);
    }

    /**
     * Test count of users in Users header.
     */
    public function testUserCountHeader()
    {
        parent::setUpPage();
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
        $link->click();
        $text = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='userdata']/table/thead/tr[1]/th[1]"))->getText();
        $this->assertEquals("Username 2 of 2", $text);
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
        $this->webDriver->findElement(WebDriverBy::linkText('Flush caches'))->click();
        parent::waitOnAjax();
        $new_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
        $this->assertNotEquals($orig_css, $new_css);
    }

}