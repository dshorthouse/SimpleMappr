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
require_once('jsmin.php');
require_once('cssmin.php');

class HEADER {

  private $js_header = array();
  private $css_header = array();

  /*
  * An array of all javascript files to be minified
  */
  public static $local_js_files = array(
    'jquery'    => 'public/javascript/jquery-1.7.1.min.js',
    'jquery-ui' => 'public/javascript/jquery-ui-1.8.16.min.js',
    'color'     => 'public/javascript/jquery.colorpicker.min.js',
    'jcrop'     => 'public/javascript/jquery.Jcrop.min.js',
    'textarea'  => 'public/javascript/jquery.textarearesizer.compressed.js',
    'cookie'    => 'public/javascript/jquery.cookie.min.js',
    'download'  => 'public/javascript/jquery.download.min.js',
    'clearform' => 'public/javascript/jquery.clearform.min.js',
    'tipsy'     => 'public/javascript/jquery.tipsy.min.js',
    'filter'    => 'public/javascript/jquery.uitablefilter.min.js',
    'hotkeys'   => 'public/javascript/jquery.hotkeys.min.js',
    'slider'    => 'public/javascript/jquery.tinycircleslider.min.js',
    'mappr'     => 'public/javascript/mappr.min.js'
  );

  /*
  * An array of all css files to be minified
  */
  public static $local_css_files = array(
    'public/stylesheets/raw/screen.css'
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
  private function file_cached($dir, $x='js') {
    $files = array_diff(@scandir($dir), array(".", "..", ".DS_Store"));
    foreach($files as $file) {
      if(($x) ? preg_match('/\.'.$x.'$/i', $file) : 1) { return $file; }
    }
    return false;
  }

  /*
  * Add javascript file(s) from remote CDN
  */
  private function remote_js_files() {
    if(ENVIRONMENT == "production") {
      foreach(self::$local_js_files as $key => $value) {
        if ($value == 'public/javascript/jquery-1.7.1.min.js') {
          unset(self::$local_js_files[$key]);
        }
      }
      $this->addJS("jquery", "http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js");
    }
    return $this;
  }

  /*
  * Add existing, minified javascript to header or create if does not already exist
  */
  private function local_js_files() {
    if(ENVIRONMENT == "production") {
      $cached_js = $this->file_cached(MAPPR_DIRECTORY . "/public/javascript/cache/");

      if (!$cached_js) {
        $js_contents = '';
        foreach(self::$local_js_files as $js_file) {
          $js_contents .= file_get_contents($js_file) . ";\n";
        }

        $js_min = JSMin::minify($js_contents);
        $js_min_file = md5(time()) . ".js";
        $handle = fopen(MAPPR_DIRECTORY . "/public/javascript/cache/" . $js_min_file, 'x+');
        fwrite($handle, $js_min);
        fclose($handle);

        $this->addJS("compiled", MAPPR_MAPS_URL . "/public/javascript/cache/" . $js_min_file);
      } else {
        $this->addJS("compiled", MAPPR_MAPS_URL . "/public/javascript/cache/" . $cached_js);
      }
    } else {
      foreach(self::$local_js_files as $key => $js_file) {
        $this->addJS($key, $js_file);
      }
    }
    return $this;
  }

  /*
  * Add existing, minified css to header or create if does not already exist
  */
  private function local_css_files() {
    if(ENVIRONMENT == "production") {
      $cached_css = $this->file_cached(MAPPR_DIRECTORY . "/public/stylesheets/cache/", "css");

      if(!$cached_css) {
        $css_min = '';
        foreach(self::$local_css_files as $css_file) {
          $css_min = CssMin::minify(file_get_contents($css_file)) . "\n";
        }
        $css_min_file = md5(time()) . ".css";
        $handle = fopen(MAPPR_DIRECTORY . "/public/stylesheets/cache/" . $css_min_file, 'x+');
        fwrite($handle, $css_min);
        fclose($handle);

        $this->addCSS('<link type="text/css" href="'.MAPPR_MAPS_URL.'/public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" />');
      } else {
        $this->addCSS('<link type="text/css" href="'.MAPPR_MAPS_URL.'/public/stylesheets/cache/' . $cached_css . '" rel="stylesheet" />');
      }

    } else {
      foreach(self::$local_css_files as $css_file) {
        $this->addCSS('<link type="text/css" href="' . MAPPR_MAPS_URL . '/' . $css_file . '" rel="stylesheet" />');
      }
    }
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
  public function getJSHeader() {
    $header  = "<script type=\"text/javascript\" src=\"".MAPPR_MAPS_URL."/public/javascript/head.load.min.js\"></script>" . "\n";
    $header .= "<script type=\"text/javascript\">";
    $header .= "head.js(";
    $counter = 1;
    foreach($this->js_header as $key => $file) {
      $header .= "{\"" . $key . "\" : \"" . $file . "\"}";
      if($counter < count($this->js_header)) { $header .= ","; }
      $counter++;
    }
    $header .= ");" . "\n";
    $session = (isset($_SESSION['simplemappr'])) ? "\"true\"" : "\"false\"";
    $key = (ENVIRONMENT == "production") ? "compiled" : "mappr";
    $header .= "head.ready(\"" . $key . "\", function(){ jQuery.extend(Mappr.settings, { \"baseUrl\" : \"http://".$_SERVER['HTTP_HOST']."\", \"active\" : " . $session . "}); });" . "\n";
    $header .= "</script>" . "\n";
    echo $header;
  }

  public function getJSFooter() {
    $foot = "";
    if(!isset($_SESSION['simplemappr'])) {
      $foot .= $this->getJanrain();
    }
    $foot .= $this->getAnalytics();
    echo $foot;
  }

  /*
  * Create Janrain inline javascript
  */
  private function getJanrain() {
    $lang = isset($_GET["lang"]) ? $_GET["lang"] : "";
    $lang_q = isset($_GET["lang"]) ? "?lang=" . $_GET["lang"] : "";
    $janrain  = "<script type=\"text/javascript\">" . "\n";
    $janrain .= "(function(w,d,s,id) {
var js, jrs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) {return;}
if (typeof w.janrain !== 'object') { w.janrain = {}; }
w.janrain.settings = {};
w.janrain.settings.language = '" . $lang . "';
w.janrain.settings.tokenUrl = 'http://" . $_SERVER['HTTP_HOST'] . "/session/" . $lang_q . "';
function isJanrainReady() { janrain.ready = true; };
if (d.addEventListener) { d.addEventListener(\"DOMContentLoaded\", isJanrainReady, false); }
else if (w.attachEvent) { w.attachEvent('onload', isJanrainReady); }
else if (w.onLoad) { w.onload = isJanrainReady; }
js = d.createElement(s);
js.id = id; js.src = (d.location.protocol === 'https:') ? 'https://rpxnow.com/js/lib/simplemappr/engage.js' : 'http://widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js';
jrs.parentNode.insertBefore(js, jrs);
})(window,document,'script','janrainAuthWidget');" . "\n";
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
      $analytics .= "_gaq.push(['_setAccount', '".GOOGLE_ANALYTICS."']); _gaq.push(['_trackPageview']);" . "\n";
      $analytics .= "(function(d,s,id) {
var js, gjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) {return;}
js = d.createElement(s); js.id = id;
js.src = '//google-analytics.com/ga.js';
gjs.parentNode.insertBefore(js, gjs);
})(document,'script','ga-analytics');" . "\n";
      $analytics .= "</script>" . "\n";
    }
    return $analytics;
  }

}
?>