<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml
 */

require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
 
class MapprTest extends WebTestCase {

  protected $url;

  public function setUp() {
    $this->url = "http://" . MAPPR_DOMAIN . "/";
  }

  public function test_response() {
    echo "----> Testing GET on " . $this->url . "\n";
    $this->get($this->url);
    $this->assertResponse(200);
    $this->assertTitle("SimpleMappr");
  }

  public function test_translation() {
    echo "----> Testing GET on " . $this->url . "?locale=fr_FR for translation" . "\n";
    $this->get($this->url . "?locale=fr_FR");
    $this->assertText("cartes point pour la publication et présentation");
  }

}

?>