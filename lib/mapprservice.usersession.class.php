<?php

/**************************************************************************

File: mapprservice.usersession.class.php

Description: Creates and destroys session

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/

require_once(dirname(dirname(__FILE__)).'/config/conf.php');
require_once(MAPPR_DIRECTORY.'/config/conf.db.php');
require_once(MAPPR_DIRECTORY.'/lib/db.class.php');

class USERSESSION {

  public static $accepted_languages = array(
    'en' => array(
      'native' => 'English',
      'code'   => 'en_US.UTF-8'),
    'fr' => array(
      'native' => 'Français',
      'code'   => 'fr_FR.UTF-8'),
/*
    'es' => array(
      'native' => 'Español',
      'code'   => 'es_ES.UTF-8')
*/
  );

  public static $domain = "messages";

  private $_token;

  private $_lang;

  private $_lang_code;

  private $_auth_info = array();

  /*
  * Create a user's session
  */
  public static function set_session() {
    session_start();
  }

  /*
  * Destroy a user's session and the simplemappr cookie
  */
  public static function destroy() {
    self::set_session();
    $lang = $_SESSION['simplemappr']['lang'];
    session_unset();
    session_destroy();
    setcookie("simplemappr", "", time() - 3600, "/");
    header('Location: http://' . $_SERVER['SERVER_NAME'] . self::make_lang_param($lang));
  }

  /*
  * Update the access field in the db
  * @param int $uid
  */
  public static function update_activity() {
    if(isset($_GET["lang"]) && !array_key_exists($_GET["lang"], self::$accepted_languages)) {
      header('HTTP/1.0 404 Not Found');
      readfile(MAPPR_DIRECTORY.'/error/404.html');
      exit();
    }

    $cookie = isset($_COOKIE["simplemappr"]) ? (array)json_decode(stripslashes($_COOKIE["simplemappr"])) : array("lang" => "en");

    if(isset($_GET["lang"])) {
      $cookie["lang"] = $_GET["lang"];
    }

    if($cookie["lang"] == "en" && isset($_GET["lang"])) {
      if(isset($_COOKIE["simplemappr"])) { setcookie("simplemappr", json_encode($cookie), COOKIE_TIMEOUT, "/"); }
      header("Location: http://".$_SERVER["SERVER_NAME"]);
      exit();
    } else if($cookie["lang"] != "en" && !isset($_GET["lang"])) {
      header("Location: http://".$_SERVER["SERVER_NAME"].USERSESSION::make_lang_param($cookie["lang"]));
      exit();
    }

    self::select_language();

    if(!isset($_COOKIE["simplemappr"])) { return; }

    self::set_session();

    $_SESSION["simplemappr"] = $cookie;
    setcookie("simplemappr", json_encode($cookie), COOKIE_TIMEOUT, "/");

    $db = new Database(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    $db->query_update('users', array('access' => time()), 'uid='.$db->escape($_SESSION["simplemappr"]["uid"]));
  }

  public static function make_lang_param($lang = "") {
    $param = "";
    if($lang && $lang != 'en') { $param = "/?lang=" . $lang; }
    return $param;
  }

  public static function select_language() {
    if(isset($_REQUEST["lang"]) && array_key_exists($_REQUEST["lang"], self::$accepted_languages)) {
      putenv('LC_ALL='.self::$accepted_languages[$_REQUEST["lang"]]['code']);
      setlocale(LC_ALL, self::$accepted_languages[$_REQUEST["lang"]]['code']);
      bindtextdomain(self::$domain, MAPPR_DIRECTORY."/i18n");
      bind_textdomain_codeset(self::$domain, 'UTF-8'); 
      textdomain(self::$domain);
    } else {
      putenv('LC_ALL='.self::$accepted_languages['en']['code']);
      setlocale(LC_ALL, self::$accepted_languages['en']['code']);
      bindtextdomain(self::$domain, MAPPR_DIRECTORY."/i18n");
      bind_textdomain_codeset(self::$domain, 'UTF-8'); 
      textdomain(self::$domain);
    }
  }

  function __construct() {
    $this->execute();
  }

  private function execute() {
    $this->get_language()
         ->get_token()
         ->make_call()
         ->make_session();
  }

  private function get_language() {
    $this->_lang = $this->load_param('lang', 'en');
    $this->_lang_code = (array_key_exists($this->_lang, self::$accepted_languages)) ? self::$accepted_languages[$this->_lang]['code'] : 'en_US.UTF-8';
    return $this;
  }

  private function get_token() {
    $this->_token = $this->load_param('token', null);
    if($this->_token) { return $this; } else { exit(); }
  }

  /*
  * Execute POST to Janrain (formerly RPXNOW) to obtain OpenID account information
  */
  private function make_call() {
    $post_data = array('token'  => $this->_token,
                       'apiKey' => RPX_KEY,
                       'format' => 'json');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $raw_json = curl_exec($curl);
    curl_close($curl);

    $this->_auth_info = json_decode($raw_json, true);

    return $this;
  }

  /*
  * Create a session and set a cookie
  */
  private function make_session() {
    if (isset($this->_auth_info['stat']) && $this->_auth_info['stat'] == 'ok') {

      $profile = $this->_auth_info['profile'];

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
        u.surname
      FROM 
        users u 
      WHERE  
        u.identifier = '".$db->escape($identifier)."'";

      $record = $db->query_first($sql);
      $user['uid'] = (!$record['uid']) ? $db->query_insert('users', $user) : $record['uid'];
      $user['lang'] = $this->_lang;

      setcookie("simplemappr", json_encode($user), COOKIE_TIMEOUT, "/");
      self::set_session();
      $_SESSION['simplemappr'] = $user;

      $db->query_update('users', array('access' => time()), 'uid='.$db->escape($user['uid']));

      header('Location: http://' . $_SERVER['SERVER_NAME'] . self::make_lang_param($user['lang']));
    } else {
      // echo 'An error occured: ' . $this->_auth_info['err']['msg'];
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