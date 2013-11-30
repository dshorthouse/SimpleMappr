<?php

/**
 * Unit tests for navigation/routes
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */

class NavigationTest extends SimpleMapprTest {

  public function setUp() {
    parent::setUp();
  }
  
  public function tearDown() {
    parent::tearDown();
  }

  public function testTagline() {
    parent::setUpPage();
    $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
    $this->assertEquals('point maps for publication and presentation', $tagline->getText());
  }

  public function testTaglineFrench() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Français'));
    $link->click();
    $tagline = $this->webDriver->findElement(WebDriverBy::id('site-tagline'));
    $this->assertEquals('cartes point pour la publication et présentation', $tagline->getText());
  }

  public function testSignInPage() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Sign In'));
    $link->click();
    parent::waitOnSpinner();
    $tagline = $this->webDriver->findElement(WebDriverBy::id('map-mymaps'));
    $this->assertContains('Save and reload your map data or create a generic template.', $tagline->getText());
  }

  public function testAPIPage() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('API'));
    $link->click();
    parent::waitOnSpinner();
    $content = $this->webDriver->findElement(WebDriverBy::id('general-api'));
    $this->assertContains('A simple, restful API may be used with Internet accessible', $content->getText());
  }

  public function testAboutPage() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('About'));
    $link->click();
    parent::waitOnSpinner();
    $content = $this->webDriver->findElement(WebDriverBy::id('general-about'));
    $this->assertContains('Create greyscale point maps suitable for reproduction on print media', $content->getText());
  }

  public function testHelpPage() {
    parent::setUpPage();
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Help'));
    $link->click();
    parent::waitOnSpinner();
    $content = $this->webDriver->findElement(WebDriverBy::id('map-help'));
    $this->assertContains('This application makes heavy use of JavaScript.', $content->getText());
  }

  public function testUserPage() {
    parent::setUpPage();
    $this->setSession('user', 'fr_FR');
    $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'user');
    $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-session'))->getText(), 'Déconnectez');

    $link = $this->webDriver->findElement(WebDriverBy::linkText('Mes cartes'));
    $link->click();
    parent::waitOnSpinner();
    $content = $this->webDriver->findElement(WebDriverBy::id('mymaps'));
    $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->getText());
    $this->assertCount(0, $this->webDriver->findElements(WebDriverBy::linkText('Administration')));
  }

  public function testAdminPage() {
    parent::setUpPage();
    $this->setSession('administrator');
    $link = $this->webDriver->findElement(WebDriverBy::linkText('Users'));
    $link->click();
    parent::waitOnSpinner();
    $this->assertEquals($this->webDriver->findElement(WebDriverBy::id('site-user'))->getText(), 'administrator');

    $matcher = array(
      'tag' => 'tbody',
      'parent' => array('attributes' => array('class' => 'grid-users')),
      'ancestor' => array('id' => 'userdata'),
      'children' => array('count' => 2)
    );
    $this->assertTag($matcher, $this->webDriver->getPageSource());

    $link = $this->webDriver->findElement(WebDriverBy::linkText('Administration'));
    $link->click();
    parent::waitOnSpinner();
    $matcher = array(
      'tag' => 'textarea',
      'id' => 'citation-reference',
      'ancestor' => array('id' => 'map-admin')
    );
    $this->assertTag($matcher, $this->webDriver->getPageSource());
  }

  public function testSource() {
    $this->webDriver->get($this->url);
    echo "\n" . $this->webDriver->getPageSource() . "\n";
  }

  public function testFlushCache() {
    parent::setUpPage();
    $this->setSession('administrator');
    $orig_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
    $this->webDriver->findElement(WebDriverBy::linkText('Administration'))->click();
    parent::waitOnSpinner();
    $this->webDriver->findElement(WebDriverBy::linkText('Flush caches'))->click();
    $this->webDriver->wait(10)->until(WebDriverExpectedCondition::alertIsPresent());
    $dialog = $this->webDriver->switchTo()->alert();
    $this->assertEquals('Caches flushed', $dialog->getText());
    $dialog->accept();
/*
    $this->webDriver->wait(10)->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::alertIsPresent()));
    $this->webDriver->navigate()->refresh();
    $new_css = $this->webDriver->findElement(WebDriverBy::xpath("//link[@type='text/css']"))->getAttribute('href');
    $this->assertNotEquals($orig_css, $new_css);
*/
  }

  private function setSession($username = "user", $locale = 'en_US') {
    $user = array(
      "identifier" => $username,
      "username" => $username,
      "email" => "nowhere@example.com",
      "locale" => $locale
    );
    $role = ($username == 'administrator') ? array("role" => "2", "uid" => "1") : array("role" => "1", "uid" => "2");
    $user = array_merge($user, $role);
    $cookie = array(
      'name' => 'simplemappr',
      'value' => urlencode(json_encode($user)),
      'path' => '/'
    );
    $this->webDriver->manage()->addCookie($cookie);
    session_cache_limiter('nocache');
    session_start();
    session_regenerate_id();
    $_SESSION["simplemappr"] = $user;
    session_write_close();
    $this->webDriver->navigate()->refresh();
  }

}

?>