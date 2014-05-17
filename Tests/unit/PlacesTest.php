<?php

/**
 * Unit tests for static methods and set-up of Places class
 */

class PlacesTest extends SimpleMapprTest {

  public function setUp() {
    $_SERVER['REQUEST_METHOD'] = 'GET';
  }

  public function tearDown() {
    unset($_SERVER['REQUEST_METHOD']);
  }

/*
  public function test_CountryList() {
    ob_start();
    $places = new \SimpleMappr\Places();
    $output = ob_get_contents();
    ob_end_clean();
    $this->assertEquals('This is the output', $output);
  }

  public function test_CountrySearch() {
  }
*/

}