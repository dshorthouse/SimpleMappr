<?php

/**************************************************************************

File: header.class.php

Description: Config HTML header class for SimpleMappr. 

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once('session.class.php');
require_once('cssmin.php');

class Header {

  private $js_header = array();
  private $css_header = array();
  private $hash = "";

  /*
  * An array of all javascript files to be minified
  */
  public static $local_js_files = array(
    'jquery'    => 'public/javascript/jquery-1.8.3.min.js',
    'jquery_ui' => 'public/javascript/jquery-ui-1.8.24.min.js',
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
    'mappr'     => 'public/javascript/mappr.min.js'
  );

  public static $remote_js_files = array(
    'jquery'    => '//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js',
    'jquery_ui' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js',
    'janrain'   => '//widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js'
  );

  /*
  * An array of all css files to be minified
  */
  public static $local_css_files = array(
    'public/stylesheets/raw/styles.css'
  );

  function __construct() {
    $this->make_hash()
         ->remote_js_files()
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

  private function make_hash() {
    if(ENVIRONMENT == "production") {
      $this->hash = md5(microtime());
    }
    return $this;
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
      $cached_js = $this->files_cached($_SERVER["DOCUMENT_ROOT"] . "/public/javascript/cache/");

      if (!$cached_js) {
        $js_contents = '';
        foreach(self::$local_js_files as $js_file) {
          $js_contents .= file_get_contents($js_file) . "\n";
        }

        $js_min_file = $this->hash . ".js";
        $handle = fopen($_SERVER["DOCUMENT_ROOT"] . "/public/javascript/cache/" . $js_min_file, 'x+');
        fwrite($handle, $js_contents);
        fclose($handle);

        $this->addJS("compiled", "public/javascript/cache/" . $js_min_file);
      } else {
        $this->addJS("compiled", "public/javascript/cache/" . $cached_js[0]);
      }
      $this->addJS("ga", "//google-analytics.com/ga.js");
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
      $cached_css = $this->files_cached($_SERVER["DOCUMENT_ROOT"] . "/public/stylesheets/cache/", "css");

      if(!$cached_css) {
        $css_min = '';
        foreach(self::$local_css_files as $css_file) {
          $css_min .= CssMin::minify(file_get_contents($css_file)) . "\n";
        }
        $css_min_file = $this->hash . ".css";
        $handle = fopen($_SERVER["DOCUMENT_ROOT"] . "/public/stylesheets/cache/" . $css_min_file, 'x+');
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

  public function getHash() {
    $cache = $this->files_cached($_SERVER["DOCUMENT_ROOT"] . "/public/stylesheets/cache/", "css");
    if($cache) {
      list($hash, $extension) = explode(".", $cache[0]);
    } else {
      $hash = "1";
    }
    return $hash;
  }

  /*
  * Create the css header
  */
  public function getCSSHeader() {
    echo implode("\n", $this->css_header) . "\n";
  }

  /*
  * DNS Prefetching
  */
  public function getDNSPrefetch() {
    $header  = "<link href=\"" . MAPPR_MAPS_URL . "\" rel=\"dns-prefetch\" />" . "\n";
    $header .= "<link href=\"" . MAPPR_MAPS_URL . "\" rel=\"prefetch\" />" . "\n";
    echo $header;
  }

  /*
  * Create the javascript header
  */
  public function getJSFooter() {
    $header  = "<script src=\"public/javascript/head.load.min.js\"></script>" . "\n";
    $header .= "<script>";
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
    $header .= "head.ready(\"".$namespace."\", function () { $.extend(Mappr.settings, { \"baseUrl\" : \"http://".$_SERVER['HTTP_HOST']."\", \"active\" : " . $session . " }); });";
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
    $janrain  = "<script async>" . "\n";
    $janrain .= "(function(w,d) {
if (typeof w.janrain !== 'object') { w.janrain = {}; }
w.janrain.settings = {};
w.janrain.settings.language = '" . Session::$accepted_locales[$locale]['canonical'] . "';
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
      $analytics  = "<script>" . "\n";
      $analytics .= "var _gaq = _gaq || [];" . "\n";
      $analytics .= "_gaq.push(['_setAccount', '".GOOGLE_ANALYTICS."'], ['_setDomainName', '".MAPPR_DOMAIN."'], ['_trackPageview']);" . "\n";
      $analytics .= "</script>" . "\n";
    }
    return $analytics;
  }

  private function getLocale() {
    return isset($_GET["locale"]) ? $_GET["locale"] : "en_US";
  }

}
?>