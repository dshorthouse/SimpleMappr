<?php

/**
 * Unit tests for Mappr class
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 */
 
class NavigationTest extends DatabaseTest {

  protected $app_url;

  public function setUp() {
    $this->app_url = "http://" . MAPPR_DOMAIN . "/";
    $this->setBrowser('firefox');
    $this->setBrowserUrl($this->app_url);
  }

  public function testTranslation() {
    $this->url($this->app_url);
    $link = $this->byLinkText('Français');
    $link->click();
    sleep(1);
    $tagline = $this->byId('site-tagline');
    $this->assertEquals('cartes point pour la publication et présentation', $tagline->text());
  }

  public function testSignInPage() {
    $this->url($this->app_url);
    $link = $this->byLinkText('Sign In');
    $link->click();
    sleep(1);
    $tagline = $this->byId('map-mymaps');
    $this->assertContains('Save and reload your map data or create a generic template.', $tagline->text());
  }

  public function testAPIPage() {
    $this->url($this->app_url);
    $link = $this->byLinkText('API');
    $link->click();
    sleep(1);
    $content = $this->byId('general-api');
    $this->assertContains('A simple, restful API may be used with Internet accessible', $content->text());
  }

  public function testAboutPage() {
    $this->url($this->app_url);
    $link = $this->byLinkText('About');
    $link->click();
    sleep(1);
    $content = $this->byId('general-about');
    $this->assertContains('Create greyscale point maps suitable for reproduction on print media', $content->text());
  }

  public function testHelpPage() {
    $this->url($this->app_url);
    $link = $this->byLinkText('Help');
    $link->click();
    sleep(1);
    $content = $this->byId('map-help');
    $this->assertContains('This application makes heavy use of JavaScript.', $content->text());
  }
  
  public function testUserPage() {
    $this->url($this->app_url . '/404'); //visit 404 page to first set cookie
    $cookie = $this->setCookie('user', 'fr_FR');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->url($this->app_url);
    $this->assertEquals($this->byId('site-user')->text(), 'user');
    $this->assertEquals($this->byId('site-session')->text(), 'Déconnectez');
    $link = $this->byLinkText('Mes cartes');
    $link->click();
    sleep(1);
    $content = $this->byId('mymaps');
    $this->assertContains('Alternativement, vous pouvez créer et enregistrer un modèle générique sans points de données', $content->text());
  }

  public function testAdminPage() {
    $this->url($this->app_url . '/404'); //visit 404 page to first set cookie
    $cookie = $this->setCookie('admin');
    $this->assertEquals($cookie, $this->cookie()->get('simplemappr'));
    $this->url($this->app_url);
    $link = $this->byLinkText('Users');
    $link->click();
    sleep(1);
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
    sleep(1);
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
            ->secure(FALSE)
            ->domain(MAPPR_DOMAIN)
            ->set();
    return $cookie;
  }
}

?>