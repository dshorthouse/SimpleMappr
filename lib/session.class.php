<?php

/**************************************************************************

File: session.class.php

Description: Creates and destroys session

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once($config_dir.'conf.db.php');
require_once('db.class.php');

class Session {

  public static $accepted_locales = array(
    'en_US' => array(
      'canonical' => 'en',
         'locale' => 'en_US',
         'native' => 'English',
         'code'   => 'en_US.UTF-8'),
    'fr_FR' => array(
      'canonical' => 'fr',
         'locale' => 'fr_FR',
         'native' => 'Français',
         'code'   => 'fr_FR.UTF-8'),
  );
  public static $domain = "messages";

  private $token;
  private $locale;
  private $locale_code;
  private $auth_info = array();

  /*
  * Create a user's session
  */
  public static function set_session() {
    session_cache_limiter('nocache');
    session_start();
    session_regenerate_id();
  }

  /*
  * Close writing to user's session
  */
  public static function close_session() {
    session_write_close();
  }

  /*
  * Destroy a user's session and the simplemappr cookie
  */
  public static function destroy() {
    self::set_session();
    $locale = isset($_SESSION['simplemappr']) ? $_SESSION['simplemappr']['locale'] : null;
    session_unset();
    session_destroy();
    setcookie("simplemappr", "", time() - 3600, "/", MAPPR_DOMAIN);
    self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($locale));
  }

  /*
  * Update the access field in the db
  * @param int $uid
  */
  public static function update_activity() {
    if(isset($_REQUEST["locale"]) && !array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
      header('HTTP/1.0 404 Not Found');
      readfile($_SERVER["DOCUMENT_ROOT"].'/error/404.html');
      exit();
    }

    $cookie = isset($_COOKIE["simplemappr"]) ? (array)json_decode(stripslashes($_COOKIE["simplemappr"])) : array("locale" => "en_US");

    if(!isset($_REQUEST["locale"]) && $cookie["locale"] != "en_US") {
      self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($cookie["locale"]));
    } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] == "en_US") {
      if(isset($_COOKIE["simplemappr"])) {
        $cookie["locale"] = "en_US";
        setcookie("simplemappr", json_encode($cookie), COOKIE_TIMEOUT, "/", MAPPR_DOMAIN);
      }
      self::redirect("http://" . MAPPR_DOMAIN);
    } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] != "en_US") {
      $cookie["locale"] = $_REQUEST["locale"];
    }

    self::select_locale();

    if(!isset($_COOKIE["simplemappr"])) { return; }

    self::write_session($cookie);

    $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    $db->query_update('users', array('access' => time()), 'uid='.$db->escape($_SESSION["simplemappr"]["uid"]));
  }

  public static function redirect($url) {
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("HTTP/1.1 303 See Other");
    header("Location: " . $url);
    exit();
  }

  public static function make_locale_param($locale = "") {
    $param = "";
    if($locale && $locale != "en_US") { $param = "/?locale=" . $locale; }
    return $param;
  }

  public static function select_locale() {
    if(isset($_REQUEST["locale"]) && array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
      putenv('LC_ALL='.self::$accepted_locales[$_REQUEST["locale"]]['code']);
      setlocale(LC_ALL, self::$accepted_locales[$_REQUEST["locale"]]['code']);
      bindtextdomain(self::$domain, $_SERVER["DOCUMENT_ROOT"]."/i18n");
      bind_textdomain_codeset(self::$domain, 'UTF-8'); 
      textdomain(self::$domain);
      return self::$accepted_locales[$_REQUEST["locale"]];
    } else {
      putenv('LC_ALL='.self::$accepted_locales['en_US']['code']);
      setlocale(LC_ALL, self::$accepted_locales['en_US']['code']);
      bindtextdomain(self::$domain, $_SERVER["DOCUMENT_ROOT"]."/i18n");
      bind_textdomain_codeset(self::$domain, 'UTF-8'); 
      textdomain(self::$domain);
      return self::$accepted_locales['en_US'];
    }
  }

  public static function write_session($data) {
    self::set_session();
    $_SESSION["simplemappr"] = $data;
    self::close_session();
    setcookie("simplemappr", json_encode($data), COOKIE_TIMEOUT, "/", MAPPR_DOMAIN);
  }

  function __construct($new_session) {
    if($new_session) {
      $this->execute();
    } else {
      self::destroy();
    }
  }

  private function execute() {
    $this->get_locale()
         ->get_token()
         ->make_call()
         ->make_session();
  }

  private function get_locale() {
    $this->locale = $this->load_param('locale', 'en_US');
    $this->locale_code = (array_key_exists($this->locale, self::$accepted_locales)) ? self::$accepted_locales[$this->locale]['code'] : 'en_US.UTF-8';
    return $this;
  }

  private function get_token() {
    $this->token = $this->load_param('token', null);
    if($this->token) { return $this; } else { self::redirect("http://" . MAPPR_DOMAIN); }
  }

  /*
  * Execute POST to Janrain (formerly RPXNOW) to obtain OpenID account information
  */
  private function make_call() {
    $post_data = array('token'  => $this->token,
                       'apiKey' => RPX_KEY,
                       'format' => 'json');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    $raw_json = curl_exec($curl);
    if ($raw_json == false) {
      echo "\n".'Curl error: ' . curl_error($curl);
      echo "\n".'HTTP code: ' . curl_errno($curl);
    }
    curl_close($curl);

    $this->auth_info = json_decode($raw_json, true);

    return $this;
  }

  /*
  * Create a session and set a cookie
  */
  private function make_session() {
    if (isset($this->auth_info['stat']) && $this->auth_info['stat'] == 'ok') {

      $profile = $this->auth_info['profile'];

      $identifier = $profile['identifier'];
      $username   = (isset($profile['preferredUsername'])) ? $profile['preferredUsername'] : '';
      $email      = (isset($profile['email'])) ? $profile['email'] : '';
      $givenname  = (isset($profile['givenName'])) ? $profile['givenName'] : '';
      $surname    = (isset($profile['familyName'])) ? $profile['familyName'] : '';

      $user = array(
        'identifier' => $identifier,
        'username'   => $username,
        'givenname'  => $givenname,
        'surname'    => $surname,
        'email'      => $email
      );

      $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

      $sql = "
      SELECT
        u.uid,
        u.identifier,
        u.email,
        u.username,
        u.givenname,
        u.surname,
        u.role
      FROM 
        users u 
      WHERE  
        u.identifier = '".$db->escape($identifier)."'";

      $record = $db->query_first($sql);
      $user['uid'] = (!$record['uid']) ? $db->query_insert('users', $user) : $record['uid'];
      $user['locale'] = $this->locale;
      $user['role'] = (!$record['role']) ? 1 : $record['role'];

      $db->query_update('users', array('access' => time()), 'uid='.$db->escape($user['uid']));

      self::write_session($user);
      self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($user['locale']));

    } else {
      echo 'An error occured: ' . $this->auth_info['err']['msg'];
      exit();
    }
  }

  /**
  * Get a request parameter
  * @param string $name
  * @param string $default parameter optional
  * @return string the parameter value or empty string if null
  */
  private function load_param($name, $default = ''){
    if(!isset($_REQUEST[$name]) || !$_REQUEST[$name]) { return $default; }
    $value = $_REQUEST[$name];
    if(get_magic_quotes_gpc() != 1) { $value = $this->add_slashes_extended($value); }
    return $value;
  }

  /**
  * Add slashes to either a string or an array
  * @param string/array $arr_r
  * @return string/array
  */
  private function add_slashes_extended(&$arr_r) {
    if(is_array($arr_r)) {
      foreach ($arr_r as &$val) {
        is_array($val) ? $this->add_slashes_extended($val) : $val = addslashes($val);
      }
      unset($val);
    } else {
      $arr_r = addslashes($arr_r);
    }
    return $arr_r;
  }

}
?>