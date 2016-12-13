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
class ShareTest extends SimpleMapprTest
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
     * Test content of share list
     */
    public function testDefaultSharesList()
    {
        parent::setUpPage();
        parent::setSession();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'));
        $link->click();
        $this->assertContains("Sample Map Administrator", $this->shareContent());
    }
    
    /**
     * Test creation of share
     */
    public function testCreateShare()
    {
        parent::setUpPage();
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Share", $link->getText());
        $link->click();
        parent::waitOnAjax();
        $this->assertEquals("Unshare", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertContains("Sample Map User", $this->shareContent());
    }

    /**
     * Test removal of a share
     */
    public function testRemoveShare()
    {
        parent::setUpPage();
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Unshare", $link->getText());
        $link->click();
        parent::waitOnAjax();

        $this->assertEquals("Share", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertNotContains("Sample User Map", $this->shareContent());
    }

    /**
     * Get the content of the default share message when none exists
     */
    private function shareContent()
    {
        $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'))->click();
        return $this->webDriver->findElement(WebDriverBy::id('sharedmaps'))->getText();
    }

}