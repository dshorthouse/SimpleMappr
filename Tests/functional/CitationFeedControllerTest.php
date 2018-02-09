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
 * Test Citation feed controller for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class CitationFeedControllerTest extends SimpleMapprTestCase
{
    use SimpleMapprTestMixin;

    protected $type;
    protected $rss;

    /**
     * Parent setUp function executed before each test.
     *
     * @return void
     */
    protected function setUp()
    {
        $response = $this->httpRequest(MAPPR_URL . "/citation.rss");
        $this->rss = simplexml_load_string($response["body"]);
        $this->type = $response["mime"];
    }

    /**
     * Test response from RSS feed is xml.
     *
     * @return void
     */
    public function testCitationsFeedType()
    {
        $this->assertEquals('application/xml', $this->type);
    }

    /**
     * Test response from RSS feed has the correct title.
     *
     * @return void
     */
    public function testCitationsFeedTitle()
    {
        $title = $this->rss->channel->title;
        $this->assertEquals('SimpleMappr Recent Citations', $title);
    }

    /**
     * Test response from RSS feed has one item.
     *
     * @return void
     */
    public function testCitationsFeedItemCount()
    {
        $this->assertEquals(1, count($this->rss->channel->item));
    }
}
