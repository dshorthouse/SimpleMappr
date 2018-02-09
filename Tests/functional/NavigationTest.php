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
 * Test Navigation for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class NavigationTest extends SimpleMapprFunctionalTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test presence of tag line.
     *
     * @return void
     */
    public function testTagline()
    {
        $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
        $this->assertEquals('create free point maps for publications and presentations', $tagline->getText());
    }

    /**
     * Test translated tag line.
     *
     * @return void
     */
    public function testTaglineFrench()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Français'));
        $link->click();
        parent::waitOnAjax();
        $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
        $this->assertEquals('créez gratuitement des cartes de répartition pour publications et présentations', $tagline->getText());
        $link = $this->webDriver->findElement(WebDriverBy::linkText('English'));
        $link->click();
    }

    /**
     * Test presence of Sign In page.
     *
     * @return void
     */
    public function testSignInPage()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Sign In'));
        $link->click();
        parent::waitOnAjax();
        $tagline = $this->webDriver->findElement(WebDriverBy::id('map-mymaps'));
        $this->assertContains('Save and reload your map data or create a generic template.', $tagline->getText());
    }

    /**
     * Test presence of API page.
     *
     * @return void
     */
    public function testAPIPage()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('API'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('general-api'));
        $this->assertContains('A simple, restful API may be used with Internet accessible', $content->getText());
    }

    /**
     * Test presence of About page.
     *
     * @return void
     */
    public function testAboutPage()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('About'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('general-about'));
        $this->assertContains('Create free point maps suitable for reproduction on print media', $content->getText());
    }

    /**
     * Test presence of Help page.
     *
     * @return void
     */
    public function testHelpPage()
    {
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Help'));
        $link->click();
        parent::waitOnAjax();
        $content = $this->webDriver->findElement(WebDriverBy::id('map-help'));
        $this->assertContains('This application makes heavy use of JavaScript.', $content->getText());
    }

    /**
     * Test 404 page
     *
     * @return void
     */
    public function test404Page()
    {
        $this->webDriver->get(MAPPR_URL . "/doesnotexist");
        $content = $this->webDriver->findElement(WebDriverBy::id('error-message'));
        $this->assertContains('The page you requested was not found', $content->getText());
    }
}
