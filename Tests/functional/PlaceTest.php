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
 * Test Places for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class PlaceTest extends SimpleMapprTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test response to index of places URL.
     *
     * @return void
     */
    public function testPlaceIndex()
    {
        $response = $this->httpRequest(MAPPR_URL . "/places");
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        $html = <<<EOD
<table class="countrycodes">
<thead>
<tr>
<td class="title">Country<input class="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" /></td>
<td class="code">ISO</td>
<td class="title">State/Province</td>
<td class="code">Code</td>
<td class="example">Example</td>
</tr>
</thead>
<tbody>
<tr class="odd">
<td>Canada</td>
<td>CAN</td>
<td>Alberta</td>
<td>AB</td>
<td>CAN[AB]</td>
</tr>
</tbody>
</table>
EOD;
        $this->assertEquals(
            $html, $response['body']
        );
    }

    /**
     * Test response to a places request for a named Country.
     *
     * @return void
     */
    public function testPlaceSearch()
    {
        $response = $this->httpRequest(MAPPR_URL . "/places.json", ["term" => "Canada"]);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        $result = json_decode($response["body"]);
        $this->assertEquals("Canada", $result[0]->value);
    }
}
