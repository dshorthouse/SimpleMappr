<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Test Map Controller for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class MapControllerTest extends SimpleMapprFunctionalTestCase
{
    /**
     * Test view own maps.
     *
     * @return void
     */
    public function testIndexMaps()
    {
        parent::setSession();

        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
    }

    /**
     * Test view admin maps.
     *
     * @return void
     */
    public function testIndexAdminMaps()
    {
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('All Maps'));
        $link->click();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > table > tbody > tr'));
        $this->assertEquals(count($map_list), 3);
    }

    /**
     * Test saving a map.
     *
     * @return void
     */
    public function testCreateMap()
    {
        parent::setSession();

        $title = 'My New Map User ' . time();
        $this->webDriver->findElement(WebDriverBy::linkText('Preview'))->click();
        $link = $this->webDriver->findElements(WebDriverBy::className('toolsSave'))[0];
        $link->click();
        $this->webDriver->findElement(WebDriverBy::id('m-mapSaveTitle'))->sendKeys($title);
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Save']"))->click();
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
     *
     * @return void
     */
    public function testDeleteMap()
    {
        $user = parent::setSession();

        $title = 'Another Sample Map User';
        $mid = parent::$db->queryInsert(
            "maps", [
                'uid' => $user['uid'],
                'title' => $title,
                'map' => json_encode(['save' => ['title' => $title]]),
                'created' => time()
            ]
        );
        $this->webDriver->navigate()->refresh();
        parent::waitOnAjax();
        $delete_links = $this->webDriver->findElements(WebDriverBy::cssSelector("#usermaps > .grid-usermaps > tbody > tr > .actions > .map-delete"));
        foreach ($delete_links as $delete_link) {
            if ($delete_link->getAttribute('data-id') == $mid) {
                $delete_link->click();
                break;
            }
        }
        $delete_text = $this->webDriver->findElement(WebDriverBy::id('mapper-message-delete'))->getText();
        $this->assertContains($title, $delete_text);
        $this->webDriver->findElement(WebDriverBy::xpath("//button[text()='Delete']"))->click();
        parent::waitOnAjax();
        $map_list = $this->webDriver->findElements(WebDriverBy::cssSelector('#usermaps > .grid-usermaps > tbody > tr'));
        $this->assertEquals(count($map_list), 2);
        parent::$db->exec("DELETE FROM maps WHERE mid = ".$mid);
    }

    /**
     * Test load user map.
     *
     * @return void
     */
    public function testLoadMap()
    {
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
