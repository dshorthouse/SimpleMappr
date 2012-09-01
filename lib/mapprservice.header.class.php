<?php

/**************************************************************************

File: mapprservice.header.class.php

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
require_once('mapprservice.usersession.class.php');
require_once('cssmin.php');
require_once('jsmin.php');

class HEADER {

  private $js_header = array();
  private $css_header = array();

  /*
  * An array of all javascript files to be minified
  */
  public static $local_js_files = array(
    'jquery'    => 'public/javascript/jquery-1.8.0.min.js',
    'jquery_ui' => 'public/javascript/jquery-ui-1.8.23.min.js',
    'color'     => 'public/javascript/jquery.colorpicker.min.js',
    'jcrop'     => 'public/javascript/jquery.Jcrop.min.js',
    'textarea'  => 'public/javascript/jquery.textarearesizer.compressed.js',
    'cookie'    => 'public/javascript/jquery.cookie.min.js',
    'download'  => 'public/javascript/jquery.download.min.js',
    'clearform' => 'public/javascript/jquery.clearform.min.js',
    'tipsy'     => 'public/javascript/jquery.tipsy.min.js',
    'hotkeys'   => 'public/javascript/jquery.hotkeys.min.js',
    'slider'    => 'public/javascript/jquery.tinycircleslider.min.js',
    'jstorage'  => 'public/javascript/jstorage.min.js',
    'serialize' => 'public/javascript/jquery.serializeJSON.min.js',
    'bbq'       => 'public/javascript/jquery.ba-bbq.min.js',
    'mappr'     => 'public/javascript/mappr.js'
  );

  public static $remote_js_files = array(
    'jquery'    => 'http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js',
    'jquery_ui' => 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js',
    'janrain'   => 'http://widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js'
  );

  /*
  * An array of all css files to be minified
  */
  public static $local_css_files = array(
    'public/stylesheets/raw/styles.css'
  );

  function __construct() {
    $this->remote_js_files()
         ->local_js_files()
         ->local_css_files();
  }

  /*
  * Obtain a file name in the cache directory
  * @param string $dir
  * @param string $x
  */
  private function files_cached($dir, $x='js') {
    $allfiles = array_diff(@scandir($dir), array(".", "..", ".DS_Store"));
    $results = array();
    foreach($allfiles as $file) {
      if(($x) ? preg_match('/\.'.$x.'$/i', $file) : 1) { $results[] = $file; }
    }
    return $results;
  }

  /*
  * Add javascript file(s) from remote CDN
  */
  private function remote_js_files() {
    if(ENVIRONMENT == "production") {
      foreach(self::$local_js_files as $key => $value) {
        if ($key == 'jquery' || $key == 'jquery_ui') {
          unset(self::$local_js_files[$key]);
          $this->addJS($key, self::$remote_js_files[$key]);
        }
      }
    }
    return $this;
  }

  /*
  * Add existing, minified javascript to header or create if does not already exist
  */
  private function local_js_files() {
    if(ENVIRONMENT == "production") {
      $cached_js = $this->files_cached(MAPPR_DIRECTORY . "/public/javascript/cache/");

      if (!$cached_js) {
        $js_contents = '';
        foreach(self::$local_js_files as $js_file) {
          $js_contents .= file_get_contents($js_file) . ";\n";
        }

        $js_min = JSMin::minify($js_contents);
        $js_min_file = md5(microtime()) . ".js";
        $handle = fopen(MAPPR_DIRECTORY . "/public/javascript/cache/" . $js_min_file, 'x+');
        fwrite($handle, $js_min);
        fclose($handle);

        $this->addJS("compiled", "public/javascript/cache/" . $js_min_file);
      } else {
        $this->addJS("compiled", "public/javascript/cache/" . $cached_js[0]);
      }
      $this->addJS("ga", "http://google-analytics.com/ga.js");
    } else {
      foreach(self::$local_js_files as $key => $js_file) {
        if($key == "mappr") { $js_file = str_replace(".min", "",$js_file); }
        $this->addJS($key, $js_file);
      }
    }
    if(!isset($_SESSION['simplemappr'])) {
      $this->addJS("janrain", self::$remote_js_files["janrain"]);
    }
    return $this;
  }

  /*
  * Add existing, minified css to header or create if does not already exist
  */
  private function local_css_files() {
    if(ENVIRONMENT == "production") {
      $cached_css = $this->files_cached(MAPPR_DIRECTORY . "/public/stylesheets/cache/", "css");

      if(!$cached_css) {
        $css_min = '';
        foreach(self::$local_css_files as $css_file) {
          $css_min .= CssMin::minify(file_get_contents($css_file)) . "\n";
        }
        $css_min_file = md5(microtime()) . ".css";
        $handle = fopen(MAPPR_DIRECTORY . "/public/stylesheets/cache/" . $css_min_file, 'x+');
        fwrite($handle, $css_min);
        fclose($handle);

        $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" media="screen,print" />');
      } else {
        foreach($cached_css as $css) {
          if(substr($css, -10) !== "-print.css") {
            $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css . '" rel="stylesheet" media="screen,print" />');
            break;
          }
        }
      }

    } else {
      foreach(self::$local_css_files as $css_file) {
        $this->addCSS('<link type="text/css" href="' . $css_file . '" rel="stylesheet" media="screen,print" />');
      }
    }
    return $this;
  }

  /*
  * Add javascript file to array
  * @param string $js
  */
  private function addJS($key, $js) {
    $this->js_header[$key] = $js;
  }

  /*
  * Add css file to array
  * @param string $css
  */
  private function addCSS($css) {
    $this->css_header[] = $css;
  }


  /*
  * Create the css header
  */
  public function getCSSHeader() {
    echo implode("\n", $this->css_header) . "\n";
  }

  /*
  * Create the javascript header
  */
  public function getJSFooter() {
    $header  = "<script type=\"text/javascript\" src=\"public/javascript/head.load.min.js\"></script>" . "\n";
    $header .= "<script type=\"text/javascript\">";
    $session = (isset($_SESSION['simplemappr'])) ? "\"true\"" : "\"false\"";
    $namespace = (ENVIRONMENT == "production") ? "compiled" : "mappr";
    $header .= "head.js(";
    $counter = 1;
    foreach($this->js_header as $key => $file) {
      $header .= "{\"" . $key . "\" : \"" . $file . "\"}";
      if($counter < count($this->js_header)) { $header .= ", "; }
      $counter++;
    }
    $header .= ");" . "\n";
    $header .= "head.ready(\"".$namespace."\", function () { $.extend(Mappr.settings, { \"baseUrl\" : \"http://".$_SERVER['HTTP_HOST']."\", \"active\" : " . $session . "}); });";
    $header .= "</script>" . "\n";
    echo $header;
  }

  public function getJSVars() {
    $foot = $this->getAnalytics();
    if(!isset($_SESSION['simplemappr'])) {
      $foot .= $this->getJanrain();
    }
    echo $foot;
  }

  /*
  * Create Janrain inline javascript
  */
  private function getJanrain() {
    $locale = $this->getLocale();
    $locale_q = isset($_GET["locale"]) ? "?locale=" . $locale : "";
    $janrain  = "<script type=\"text/javascript\">" . "\n";
    $janrain .= "(function(w,d) {
if (typeof w.janrain !== 'object') { w.janrain = {}; }
w.janrain.settings = {};
w.janrain.settings.language = '" . USERSESSION::$accepted_locales[$locale]['canonical'] . "';
w.janrain.settings.tokenUrl = 'http://" . $_SERVER['HTTP_HOST'] . "/session/" . $locale_q . "';
function isJanrainReady() { janrain.ready = true; };
if (d.addEventListener) { d.addEventListener(\"DOMContentLoaded\", isJanrainReady, false); }
else if (w.attachEvent) { w.attachEvent('onload', isJanrainReady); }
else if (w.onLoad) { w.onload = isJanrainReady; }
})(window,document);" . "\n";
    $janrain .= "</script>" . "\n";
    return $janrain;
  }

  /*
  * Create Google Analytics inline javascript
  */
  private function getAnalytics() {
    $analytics = "";
    if(ENVIRONMENT == "production") {
      $analytics  = "<script type=\"text/javascript\">" . "\n";
      $analytics .= "var _gaq = _gaq || [];" . "\n";
      $analytics .= "_gaq.push(['_setAccount', '".GOOGLE_ANALYTICS."'], ['_trackPageview']);" . "\n";
      $analytics .= "</script>" . "\n";
    }
    return $analytics;
  }

  private function getLocale() {
    return isset($_GET["locale"]) ? $_GET["locale"] : "en_US";
  }

}
?>