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
 * Test Page headers for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class PageHeaderTest extends SimpleMapprFunctionalTestCase
{
    /**
     * Test page title.
     *
     * @return void
     */
    public function testPageTitle()
    {
        $title = $this->webDriver->getTitle();
        $this->assertEquals('SimpleMappr', $title);
    }

    /**
     * Test default language attribute to HTML tag.
     *
     * @return void
     */
    public function testLanguageEnglish()
    {
        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('en', $lang);
    }

    /**
     * Test lang=fr attribute for HTML tag.
     *
     * @return void
     */
    public function testLanguageFrancais()
    {
        $this->webDriver->get(MAPPR_URL . "/?locale=fr_FR");
        $lang = $this->webDriver->findElement(WebDriverBy::xpath("//html"))->getAttribute('lang');
        $this->assertEquals('fr', $lang);
        $this->webDriver->get(MAPPR_URL . "/?locale=en_US");
    }

    /**
     * Test meta description.
     *
     * @return void
     */
    public function testDescription()
    {
        $description = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='description']"))->getAttribute('content');
        $this->assertContains("Create free point maps for publications and presentations", $description);
    }

    /**
     * Test meta keywords.
     *
     * @return void
     */
    public function testKeywords()
    {
        $keywords = $this->webDriver->findElement(WebDriverBy::xpath("//meta[@name='keywords']"))->getAttribute('content');
        $this->assertContains("publication,presentation,map,georeference", $keywords);
    }

    /**
     * Test meta author.
     *
     * @return void
     */
    public function testAuthor()
    {
        $this->assertContains("David P. Shorthouse", $this->_metaElementContent("//meta[@name='author']"));
    }

    /**
     * Test OpenGraph content.
     *
     * @return void
     */
    public function testOpenGraph()
    {
        $og_title = $this->_metaElementContent("//meta[@property='og:title']");
        $og_description = $this->_metaElementContent("//meta[@property='og:description']");
        $og_locale = $this->_metaElementContent("//meta[@property='og:locale']");
        $og_type = $this->_metaElementContent("//meta[@property='og:type']");
        $og_url = $this->_metaElementContent("//meta[@property='og:url']");
        $og_image = $this->_metaElementContent("//meta[@property='og:image']");

        $this->assertContains("SimpleMappr", $og_title);
        $this->assertContains("Create free point maps for publications and presentations", $og_description);
        $this->assertContains("en_US", $og_locale);
        $this->assertContains("website", $og_type);
        $this->assertContains(MAPPR_URL, $og_url);
        $this->assertContains(MAPPR_URL . "/public/images/logo_og.png", $og_image);
    }

    /**
     * Test Twitter card
     *
     * @return void
     */
    public function testTwitterCard()
    {
        $twitter_card = $this->_metaElementContent("//meta[@name='twitter:card']");
        $twitter_site = $this->_metaElementContent("//meta[@name='twitter:site']");
        $twitter_creator = $this->_metaElementContent("//meta[@name='twitter:creator']");

        $this->assertContains("summary", $twitter_card);
        $this->assertContains("@SimpleMappr", $twitter_site);
        $this->assertContains("@dpsSpiders", $twitter_creator);
    }

    /**
     * Obtain content of an XPATH element.
     *
     * @param string $xpath The XPATH of interest.
     *
     * @return string The content.
     */
    private function _metaElementContent($xpath)
    {
        return $this->webDriver->findElement(WebDriverBy::xpath($xpath))->getAttribute('content');
    }
}
