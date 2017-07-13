<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */
class CitationFeedControllerTest extends SimpleMapprFunctionalTestCase
{
    use SimpleMapprTestMixin;

    protected $type;
    protected $rss;

    /**
     * Parent setUp function executed before each test.
     */
    protected function setUp()
    {
        $response = $this->httpRequest(MAPPR_URL . "/citation.rss");
        $this->rss = simplexml_load_string($response["body"]);
        $this->type = $response["mime"];
    }

    /**
     * Test response from RSS feed is xml.
     */
    public function testCitationsFeedType()
    {
        $this->assertEquals('application/xml', $this->type);
    }

    /**
     * Test response from RSS feed has the correct title.
     */
    public function testCitationsFeedTitle()
    {
        $this->assertEquals('SimpleMappr Recent Citations', $this->rss->channel->title);
    }

    /**
     * Test response from RSS feed has one item.
     */
    public function testCitationsFeedItemCount()
    {
        $this->assertEquals(1, count($this->rss->channel->item));
    }
}
