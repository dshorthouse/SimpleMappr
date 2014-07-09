<?php

/**
 * Unit tests for Usermap handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class UsermapTest extends SimpleMapprTest
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
     * Test view own maps.
     */
    public function testIndexUserMaps()
    {
        parent::setUpPage();
        parent::setSession();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('My Maps'));
        $link->click();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 1);
    }

    /**
     * Test view admin maps.
     */
    public function testIndexAdminMaps()
    {
        parent::setUpPage();
        parent::setSession('administrator');
        $link = $this->webDriver->findElement(WebDriverBy::linkText('All Maps'));
        $link->click();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
    }

    /**
     * Test saving a map.
     */
    public function testCreateUserMap()
    {
        $title = 'My New Map ' . time();
        parent::setUpPage();
        parent::setSession();
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsSave'))[0];
        $link->click();
        $this->webDriver->findElement(WebDriverBy::id('m-mapSaveTitle'))->sendKeys($title);
        $this->webDriver->findElement(WebDriverBy::xpath("//button/span[text()='Save']"))->click();
        parent::waitOnSpinner();
        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $saved_map_title = $this->webDriver->findElements(WebDriverBy::className('map-load'))[0];
        $this->assertEquals($title, $saved_map_title->getText());
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
    }
    
    /**
     * Test deleting a map.
     */
    public function testDeleteUserMap()
    {
        parent::setUpPage();
        parent::setSession();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('My Maps'));
        $link->click();
        $delete = $this->webDriver->findElements(WebDriverBy::className('map-delete'))[0];
        $delete->click();
        $this->webDriver->findElement(WebDriverBy::xpath("//button/span[text()='Delete']"))->click();
        parent::waitOnSpinner();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 1);
    }

    /**
     * Test load user map.
     */
    public function testLoadUserMap()
    {
        $map_title = "Sample Map 2";
        parent::setUpPage();
        parent::setSession();
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElement(WebDriverBy::linkText('My Maps'));
        $link->click();
        $map_link = $this->webDriver->findElement(WebDriverBy::linkText($map_title));
        $map_link->click();
        parent::waitOnSpinner();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('mapTitle'))->getText(), $map_title);
        $this->assertNotEquals($default_img, $new_img);
    }
}