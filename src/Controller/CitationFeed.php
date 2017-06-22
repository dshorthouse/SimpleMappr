<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2017 David P. Shorthouse
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
 *
 */
namespace SimpleMappr\Controller;

use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

/**
 * CitationFeed for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class CitationFeed extends Citation
{
    /** @var object $_feed An RSS feed Object */
    private $_feed;

    /** @var object $_channel An RSS channel Object */
    private $_channel;

    /**
     * Make the Channel
     */
    public function makeChannel()
    {
        $this->_feed = new Feed();
        $this->_channel = new Channel();
        $this->_channel
            ->title('SimpleMappr Recent Citations')
            ->description('Channel of recent publications that have used SimpleMappr')
            ->url(MAPPR_URL)
            ->language('en-US')
            ->pubDate(time())
            ->ttl(60)
            ->pubsubhubbub(MAPPR_URL . "/citation.rss", "https://pubsubhubbub.appspot.com/")
            ->appendTo($this->_feed);
        return $this;
    }

    /**
     * Add items to the channel
     *
     * @return object $this
     */
    public function addItems()
    {
        $week_ago = time() - (7 * 24 * 60 * 60);
        $entries = $this->index();
        usort($entries['citations'], function ($a, $b) {
            return $b->year > $a->year;
        });
        foreach($entries['citations'] as $citation) {
            $url = ($citation->doi) ? "https://doi.org/{$citation->doi}" : $citation->link;
            if ($url && $citation->created >= $week_ago) {
                $item = new Item();
                $item
                    ->preferCdata(true)
                    ->title($citation->reference)
                    ->description($citation->reference)
                    ->url($url)
                    ->guid($url, true)
                    ->pubDate($citation->created)
                    ->appendTo($this->_channel);
            }
        }
        return $this;
    }

    /**
     * Return the feed
     *
     * @return object $_feed
     */
    public function getFeed()
    {
        return $this->_feed;
    }

}