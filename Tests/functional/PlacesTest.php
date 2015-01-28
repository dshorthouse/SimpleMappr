<?php

/**
 * Unit tests for static methods and set-up of Places class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class PlacesTest extends SimpleMapprTest
{
    /**
     * Parent setUp function executed before each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Parent tearDown function executed after each test.
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test response to index of places URL.
     */
    public function test_PlacesIndex()
    {
        $ch = curl_init($this->url . "places");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $this->assertEquals('text/html; charset=utf-8', $type);
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
</table>', $result);
    }

    /**
     * Test response to a places request for a named Country.
     */
    public function test_PlacesSearch()
    {
        $ch = curl_init($this->url . "places.json/?term=Canada");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        $this->assertEquals('application/json', $type);
        $this->assertEquals("Canada", $result[0]->value);
    }

}