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

  public function setUpPage() {
    $this->url("/");
    $this->waitOnSpinner();
  }

  public function waitOnSpinner() {
    while($this->byId('map-loader')->displayed()) {
      sleep(1);
    }
  }

  //TODO: get parent node of $tab and verify that css class ui-tabs-loading is not present
  public function waitOnTab($tab) {
    sleep(2);
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
    $this->waitOnTab($link);
    $tagline = $this->byId('map-mymaps');
    $this->assertContains('Save and reload your map data or create a generic template.', $tagline->text());
  }

  public function testAPIPage() {
    $link = $this->byLinkText('API');
    $link->click();
    $this->waitOnTab($link);
    $content = $this->byId('general-api');
    $this->assertContains('A simple, restful API may be used with Internet accessible', $content->text());
  }

  public function testAboutPage() {
    $link = $this->byLinkText('About');
    $link->click();
    $this->waitOnTab($link);
    $content = $this->byId('general-about');
    $this->assertContains('Create greyscale point maps suitable for reproduction on print media', $content->text());
  }

  public function testHelpPage() {
    $link = $this->byLinkText('Help');
    $link->click();
    $this->waitOnTab($link);
    $content = $this->byId('map-help');
    $this->assertContains('This application makes heavy use of JavaScript.', $content->text());
  }
  
  public function testUserPage() {
    $this->prepareSession();
    $cookie = $this->setCookie('user', 'fr_FR');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->refresh();
    $this->assertEquals($this->byId('site-user')->text(), 'user');
    $this->assertEquals($this->byId('site-session')->text(), 'Déconnectez');

    $link = $this->byLinkText('Mes cartes');
    $link->click();
    $this->waitOnTab($link);
    $content = $this->byId('mymaps');
    $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->text());
  }

  public function testAdminPage() {
    $this->prepareSession();
    $cookie = $this->setCookie('admin');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->refresh();
    $link = $this->byLinkText('Users');
    $link->click();
    $this->waitOnTab($link);
    $this->assertEquals($this->byId('site-user')->text(), 'admin');

    $matcher = array(
      'tag' => 'tbody',
      'parent' => array('attributes' => array('class' => 'grid-users')),
      'ancestor' => array('id' => 'userdata'),
      'children' => array('count' => 2)
    );
    $this->assertTag($matcher, $this->source());

    $link = $this->byLinkText('Administration');
    $link->click();
    $this->waitOnTab($link);
    $matcher = array(
      'tag' => 'textarea',
      'id' => 'citation-reference',
      'ancestor' => array('id' => 'map-admin')
    );
    $this->assertTag($matcher, $this->source());
  }

  private function setCookie($role, $locale = 'en_US') {
    if($role == 'admin') {
      $cookie = urlencode('{"identifier":"admin","username":"admin","email":"nowhere@example.com","locale":"'.$locale.'","role":"2","uid":"1"}');
    } else {
      $cookie = urlencode('{"identifier":"user","username":"user","email":"nowhere@example.com","locale":"'.$locale.'","role":"1","uid":"2"}');
    }
    $cookies = $this->cookie();
    $cookies->add('simplemappr', $cookie)
            ->path('/')
            ->domain(MAPPR_DOMAIN)
            ->set();
    return $cookie;
  }
}

?>