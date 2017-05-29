<?php

/**
 * Unit tests for Map handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class MapTest extends SimpleMapprTest
{
    /**
     * Test view own maps.
     */
    public function testIndexMaps()
    {
        parent::setUpPage();
        parent::setSession();

        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
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
        $this->assertEquals(count($map_list), 3);
    }

    /**
     * Test saving a map.
     */
    public function testCreateMap()
    {
        parent::setUpPage();
        parent::setSession();

        $title = 'My New Map User ' . time();
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsSave'))[0];
        $link->click();
        $this->webDriver->findElement(WebDriverBy::id('m-mapSaveTitle'))->sendKeys($title);
        $this->webDriver->findElement(WebDriverBy::xpath("//button/span[text()='Save']"))->click();
        parent::waitOnAjax();
        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $saved_map_title = $this->webDriver->findElements(WebDriverBy::className('map-load'))[0];
        $this->assertEquals($title, $saved_map_title->getText());
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 3);
        parent::$db->exec("DELETE FROM maps where title = '".$title."'");
    }
    
    /**
     * Test deleting a map.
     */
    public function testDeleteMap()
    {
        parent::setUpPage();
        $user = parent::setSession();

        $title = 'Another Sample Map User';
        $mid = parent::$db->queryInsert("maps", [
            'uid' => $user['uid'],
            'title' => $title,
            'map' => json_encode(['save' => ['title' => $title]]),
            'created' => time()
        ]);
        $this->webDriver->navigate()->refresh();
        parent::waitOnAjax();
        $delete_links = $this->webDriver->findElements(WebDriverBy::cssSelector("#usermaps > .grid-usermaps > tbody > tr > .actions > .map-delete"));
        foreach($delete_links as $delete_link) {
            if($delete_link->getAttribute('data-id') == $mid) {
                $delete_link->click();
                break;
            }
        }
        $delete_text = $this->webDriver->findElement(WebDriverBy::id('mapper-message-delete'))->getText();
        $this->assertContains($title, $delete_text);
        $this->webDriver->findElement(WebDriverBy::xpath("//button/span[text()='Delete']"))->click();
        parent::waitOnAjax();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > .grid-usermaps > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
        parent::$db->exec("DELETE FROM maps WHERE mid = ".$mid);
    }

    /**
     * Test load user map.
     */
    public function testLoadMap()
    {
        parent::setUpPage();
        parent::setSession();
        $map_title = "Sample Map User";
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $default_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $link = $this->webDriver->findElement(WebDriverBy::linkText('My Maps'));
        $link->click();
        $map_link = $this->webDriver->findElement(WebDriverBy::linkText($map_title));
        $map_link->click();
        parent::waitOnMap();
        $new_img = $this->webDriver->findElement(WebDriverBy::id('mapOutputImage'))->getAttribute('src');
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('mapTitle'))->getText(), $map_title);
        $this->assertNotEquals($default_img, $new_img);
        $this->assertContains(MAPPR_MAPS_URL, $new_img);
    }
}