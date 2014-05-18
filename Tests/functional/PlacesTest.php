<?php

/**
 * Unit tests for static methods and set-up of Places class
 */

class PlacesTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function test_PlacesIndex() {
    $ch = curl_init($this->url . "/places/");

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    $this->assertEquals('text/html; charset=utf-8', $type);
    $this->assertEquals('<table class="countrycodes"><thead><tr><td class="title">Country<input class="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" /></td><td class="code">ISO</td><td class="title">State/Province</td><td class="code">Code</td><td class="example">Example</td></tr></thead><tbody><tr class="odd"><td>Canada</td><td>CAN</td><td>Alberta</td><td>AB</td><td>CAN[AB]</td></tr></tbody></table>', $result);
  }

  public function test_PlacesSearch() {
    $ch = curl_init($this->url . "/places/Canada");

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