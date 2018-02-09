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
 * Test roles and permissions for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Administration extends SimpleMapprFunctionalTestCase
{
    /**
     * Test count of users in Users table.
     *
     * @return void
     */
    public function testUserCountTable()
    {
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
        $link->click();
        $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'John Smith');
        $users = $this->webDriver->findElements(WebDriverBy::cssSelector('#userdata > .grid-users > tbody > tr'));
        $this->assertEquals(count($users), 2);
    }

    /**
     * Test count of users in Users header.
     *
     * @return void
     */
    public function testUserCountHeader()
    {
        parent::setSession('administrator');

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
        $link->click();
        $text = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='userdata']/table/thead/tr[1]/th[1]"))->getText();
        $this->assertEquals("Username 2 of 2", $text);
    }

    /**
     * Test flushing of caches.
     *
     * @return void
     */
    public function testFlushCache()
    {
        parent::setSession('administrator');

        $orig_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
        $this->webDriver->findElement(WebDriverBy::linkText('Administration'))->click();
        $this->webDriver->findElement(WebDriverBy::linkText('Flush caches'))->click();
        parent::waitOnAjax();
        $new_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
        $this->assertNotEquals($orig_css, $new_css);
    }
}
