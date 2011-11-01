<?php

/**************************************************************************

File: mapprservice.i18n.class.php

Description: Config HTML header class for SimpleMappr. 

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

class I18N {

  public $lang;

  public static $accepted_languages = array(
    'en' => array(
      'native' => 'English',
      'code'   => 'en_US.UTF-8'),
    'fr' => array(
      'native' => 'Français',
      'code'   => 'fr_FR.UTF-8'),
    'es' => array(
      'native' => 'Español',
      'code'   => 'es_ES.UTF8')
  );

  function __construct() {
    $this->execute();
  }

  private function execute() {
    $lang = $this->load_param('lang', 'en');
    $this->lang = (array_key_exists($lang, self::$accepted_languages)) ? self::$accepted_languages[$lang]['code'] : 'en_US.UTF-8';
    putenv('LC_ALL='.$this->lang);
    setlocale(LC_ALL, $this->lang);
    $domain = 'simplemappr';
    bindtextdomain($domain, MAPPR_DIRECTORY."/i18n");
    bind_textdomain_codeset($domain, 'UTF-8'); 
    textdomain($domain);
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