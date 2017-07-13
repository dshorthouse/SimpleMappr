<?php

/**
 * Unit tests for static methods and set-up of Place class
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */
class PlaceTest extends SimpleMapprFunctionalTestCase
{
    use SimpleMapprTestMixin;

    /**
     * Test response to index of places URL.
     */
    public function test_PlaceIndex()
    {
        $response = $this->httpRequest(MAPPR_URL . "/places");
        $this->assertEquals('text/html; charset=UTF-8', $response['mime']);
        $this->assertEquals('<table class="countrycodes">
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
</table>', $response['body']);
    }

    /**
     * Test response to a places request for a named Country.
     */
    public function test_PlaceSearch()
    {
        $response = $this->httpRequest(MAPPR_URL . "/places.json", ["term" => "Canada"]);
        $this->assertEquals('application/json; charset=UTF-8', $response['mime']);
        $result = json_decode($response["body"]);
        $this->assertEquals("Canada", $result[0]->value);
    }
}
