<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class NavigationTest extends DatabaseTest {

  protected $app_url;

  protected function setUp() {
    $this->setBrowser('firefox');
    $this->setBrowserUrl("http://" . MAPPR_DOMAIN . "/");
  }
  
  protected function tearDown() {
    Header::flush_cache(false);
  }

  public function setUpPage() {
    new Header;
    $this->url("/");
    $this->waitOnSpinner();
  }

  public function waitOnSpinner() {
    while($this->byId('map-loader')->displayed()) {
      sleep(1);
    }
  }

  public function testTranslation() {
    $link = $this->byLinkText('Français');
    $link->click();
    $tagline = $this->byId('site-tagline');
    $this->assertEquals('cartes point pour la publication et présentation', $tagline->text());
  }

  public function testSignInPage() {
    $link = $this->byLinkText('Sign In');
    $link->click();
    $this->waitOnSpinner();
    $tagline = $this->byId('map-mymaps');
    $this->assertContains('Save and reload your map data or create a generic template.', $tagline->text());
  }

  public function testAPIPage() {
    $link = $this->byLinkText('API');
    $link->click();
    $this->waitOnSpinner();
    $content = $this->byId('general-api');
    $this->assertContains('A simple, restful API may be used with Internet accessible', $content->text());
  }

  public function testAboutPage() {
    $link = $this->byLinkText('About');
    $link->click();
    $this->waitOnSpinner();
    $content = $this->byId('general-about');
    $this->assertContains('Create greyscale point maps suitable for reproduction on print media', $content->text());
  }

  public function testHelpPage() {
    $link = $this->byLinkText('Help');
    $link->click();
    $this->waitOnSpinner();
    $content = $this->byId('map-help');
    $this->assertContains('This application makes heavy use of JavaScript.', $content->text());
  }

  public function testUserPage() {
    $cookie = $this->setSession('user', 'fr_FR');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->refresh();
    $this->assertEquals($this->byId('site-user')->text(), 'user');
    $this->assertEquals($this->byId('site-session')->text(), 'Déconnectez');

    $link = $this->byLinkText('Mes cartes');
    $link->click();
    $this->waitOnSpinner();
    $content = $this->byId('mymaps');
    $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->text());
  }

  public function testAdminPage() {
    $cookie = $this->setSession('administrator');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->refresh();
    $link = $this->byLinkText('Users');
    $link->click();
    $this->waitOnSpinner();
    $this->assertEquals($this->byId('site-user')->text(), 'administrator');

    $matcher = array(
      'tag' => 'tbody',
      'parent' => array('attributes' => array('class' => 'grid-users')),
      'ancestor' => array('id' => 'userdata'),
      'children' => array('count' => 2)
    );
    $this->assertTag($matcher, $this->source());

    $link = $this->byLinkText('Administration');
    $link->click();
    $this->waitOnSpinner();
    $matcher = array(
      'tag' => 'textarea',
      'id' => 'citation-reference',
      'ancestor' => array('id' => 'map-admin')
    );
    $this->assertTag($matcher, $this->source());
  }

  public function testFlushCache() {
    $this->setSession('administrator');
    $this->refresh();
    $link = $this->byLinkText('Administration');
    $link->click();
    $this->waitOnSpinner();
    $link = $this->byLinkText('Flush caches');
    $link->click();
    $this->assertEquals('Caches flushed', $this->alertText());
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
    $cookie = urlencode(json_encode($user));
    $cookies = $this->cookie();
    $cookies->add('simplemappr', $cookie)
            ->path('/')
            ->domain(MAPPR_DOMAIN)
            ->set();
    session_cache_limiter('nocache');
    session_start();
    session_regenerate_id();
    $_SESSION["simplemappr"] = $user;
    session_write_close();
    return $cookie;
  }
}

?>