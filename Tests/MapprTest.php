<?php

require_once('DatabaseTest.php');

class MapprTest extends DatabaseTest {

    public function setUp() {
    }

    public function tearDown() {
    }

    public function testMappr() {
      $mappr = new Mappr();
      $this->assertEquals('foobar', 'foobar');
    }

}
