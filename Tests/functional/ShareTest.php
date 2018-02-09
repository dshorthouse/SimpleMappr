<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
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
 * Test sharing maps for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class ShareTest extends SimpleMapprFunctionalTestCase
{
    /**
     * Test content of share list
     *
     * @return void
     */
    public function testDefaultSharesList()
    {
        parent::setSession();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'));
        $link->click();
        $this->assertContains("Sample Map Administrator", $this->_shareContent());
    }

    /**
     * Test share count
     *
     * @return void
     */
    public function testShareCount()
    {
        parent::setSession();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'));
        $link->click();
        $text = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='sharedmaps']/table/thead/tr[1]/th[1]"))->getText();
        $this->assertEquals("Title 1", $text);
    }

    /**
     * Test creation of share
     *
     * @return void
     */
    public function testCreateShare()
    {
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Share", $link->getText());
        $link->click();
        parent::waitOnAjax();
        $this->assertEquals("Unshare", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertContains("Sample Map User", $this->_shareContent());
    }

    /**
     * Test removal of a share
     *
     * @return void
     */
    public function testRemoveShare()
    {
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Unshare", $link->getText());
        $link->click();
        parent::waitOnAjax();

        $this->assertEquals("Share", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertNotContains("Sample User Map", $this->_shareContent());
    }

    /**
     * Get the content of the default share message when none exists
     *
     * @return void
     */
    private function _shareContent()
    {
        $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'))->click();
        return $this->webDriver->findElement(WebDriverBy::id('sharedmaps'))->getText();
    }
}
