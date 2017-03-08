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
class CitationFeedTest extends SimpleMapprTest
{

    protected $type;
    protected $rss;

    /**
     * Parent setUp function executed before each test.
     */
    public function setUp()
    {
        parent::setUp();
        $ch = curl_init(MAPPR_URL . "/citation.rss");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->rss = simplexml_load_string(curl_exec($ch));
        $this->type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        parent::tearDown();
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