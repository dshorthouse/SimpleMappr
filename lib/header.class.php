<?php

/********************************************************************

header.class.php released under MIT License
Configure HTML headers for SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2010 David P. Shorthouse {{{

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

}}}

********************************************************************/

$config_dir = dirname(dirname(__FILE__)).'/config/';
require_once($config_dir.'conf.php');
require_once('session.class.php');
require_once('user.class.php');
require_once('cssmin.php');
require_once('utilities.class.php');

class Header {

  private $js_header = array();
  private $css_header = array();
  private $hash = "";
  private $redis = "";

  private static $css_cache_path = "/public/stylesheets/cache/";
  private static $js_cache_path = "/public/javascript/cache/";

  /*
  * An array of all javascript files to be minified
  */
  public $local_js_combined = array(
    'jquery'      => 'public/javascript/jquery-1.10.2.min.js',
    'jquery_ui'   => 'public/javascript/jquery-ui-1.9.2.custom.min.js',
    'color'       => 'public/javascript/jquery.colorpicker.min.js',
    'jcrop'       => 'public/javascript/jquery.Jcrop.min.js',
    'textarea'    => 'public/javascript/jquery.textarearesizer.min.js',
    'cookie'      => 'public/javascript/jquery.cookie.min.js',
    'download'    => 'public/javascript/jquery.download.min.js',
    'clearform'   => 'public/javascript/jquery.clearform.min.js',
    'tipsy'       => 'public/javascript/jquery.tipsy.min.js',
    'hotkeys'     => 'public/javascript/jquery.hotkeys.min.js',
    'slider'      => 'public/javascript/jquery.tinycircleslider.min.js',
    'jstorage'    => 'public/javascript/jstorage.min.js',
    'serialize'   => 'public/javascript/jquery.serializeJSON.min.js',
    'bbq'         => 'public/javascript/jquery.ba-bbq.min.js',
    'hashchange'  => 'public/javascript/jquery.ba-hashchange.min.js',
    'toggle'      => 'public/javascript/jquery.toggleClick.min.js',
    'simplemappr' => 'public/javascript/simplemappr.min.js'
  );

  public $admin_js = array(
    'admin' => 'public/javascript/simplemappr.admin.min.js'
  );

  public $remote_js = array(
    'jquery'    => '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js',
    'janrain'   => '//widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js'
  );

  /*
  * An array of all css files to be minified
  */
  public $local_css = array(
    'public/stylesheets/raw/styles.css'
  );

  public static function flush_cache($output = true) {
    $cached_files = array();

    $css_files = array_diff(@scandir(dirname(dirname(__FILE__)) . self::$css_cache_path), array(".", "..", ".DS_Store"));
    foreach($css_files as $file) {
      if(preg_match('/\.css$/i', $file)) { $cached_files[] = dirname(dirname(__FILE__)) . self::$css_cache_path . $file; }
    }
    $js_files = array_diff(@scandir(dirname(dirname(__FILE__)) . self::$js_cache_path), array(".", "..", ".DS_Store"));
    foreach($js_files as $file) {
      if(preg_match('/\.js$/i', $file)) { $cached_files[] = dirname(dirname(__FILE__)) . self::$js_cache_path . $file; }
    }
    foreach($cached_files as $file) {
      unlink($file);
    }

    $redis_flush = "n/a";
    try {
      if(extension_loaded("redis") && defined('REDIS_SERVER')) {
        $redis = new Redis();
        $redis->connect(REDIS_SERVER);
        $redis->delete("simplemappr_hash");
        $redis_flush = true;
      }
    } catch(Exception $e) {
      $redis_flush = false;
    }

    $cloudflare_flush = "n/a";
    if (self::cloudflare_enabled()) {
     $cloudflare_flush = (self::flush_cloudflare()) ? true : false;
    }

    if($output) {
      Utilities::set_header("json");
      $response = array(
        "files" => true,
        "redis" => $redis_flush,
        "cloudflare" => $cloudflare_flush
      );
      echo json_encode($response);
    }

  }

  public static function flush_cloudflare() {
    $URL = "https://www.cloudflare.com/api_json.html";

    $data = array(
                 "a" => "fpurge_ts",
                 "z" => CLOUDFLARE_DOMAIN,
                 "email" => CLOUDFLARE_EMAIL,
                 "tkn" => CLOUDFLARE_KEY,
                 "v" => 1
                 );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $http_result = curl_exec($ch);
    $error = curl_error($ch);

    $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($http_code == 200) {
      return true;
    }
    return false;
  }

  public static function cloudflare_enabled() {
    if(defined('CLOUDFLARE_KEY') && !empty(CLOUDFLARE_KEY)) { return true; }
    else { return false; }
  }

  function __construct() {
    $this->redis()
         ->make_hash()
         ->add_remote_js()
         ->combine_local_js()
         ->combine_local_css()
         ->set_redis();
  }

  private function redis_installed() {
    if(extension_loaded("redis")) {
      return true;
    }
    return false;
  }

  private function redis() {
    if($this->redis_installed()) {
      $this->redis = new Redis();
      $this->redis->pconnect(REDIS_SERVER);
    }
    return $this;
  }

  private function set_redis() {
    if($this->redis_installed()) {
      if(!$this->redis->exists("simplemappr_hash")) {
        $this->redis->set('simplemappr_hash', $this->hash);
      }
    }
    return $this;
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
  private function add_remote_js() {
    if(ENVIRONMENT == "production") {
      $this->addJS('jquery', $this->remote_js['jquery']);
      $this->addJS('jquery_ui', $this->local_js_combined['jquery_ui']);
    }
    return $this;
  }

  /*
  * Add existing, minified javascript to header or create if does not already exist
  */
  private function combine_local_js() {
    if(ENVIRONMENT == "production") {
      if($this->redis_installed()) {
        $cached_js = ($this->redis->exists('simplemappr_hash')) ? array($this->redis->get('simplemappr_hash') . ".js") : array();
      } else {
        $cached_js = $this->files_cached(dirname(dirname(__FILE__)) . self::$js_cache_path);
      }

      if(!$cached_js) {
        unset($this->local_js_combined['jquery'], $this->local_js_combined['jquery_ui']);
        $js_contents = '';
        foreach($this->local_js_combined as $js_file) {
          $js_contents .= file_get_contents($js_file) . "\n";
        }

        $js_min_file = $this->hash . ".js";
        $handle = fopen(dirname(dirname(__FILE__)) . self::$js_cache_path . $js_min_file, 'x+');
        fwrite($handle, $js_contents);
        fclose($handle);

        $this->addJS("compiled", self::$js_cache_path . $js_min_file);
      } else {
        foreach($cached_js as $js) {
          $this->addJS("compiled", self::$js_cache_path . $js);
        }
      }
      $this->addJS("ga", "//google-analytics.com/ga.js");
    } else {
      foreach($this->local_js_combined as $key => $js_file) {
        if($key == "simplemappr") { $js_file = str_replace(".min", "",$js_file); }
        $this->addJS($key, $js_file);
      }
    }
    if(!isset($_SESSION['simplemappr'])) {
      $this->addJS("janrain", $this->remote_js["janrain"]);
    }
    if($this->isAdministrator()) {
      foreach($this->admin_js as $key => $js_file) {
        if(ENVIRONMENT == "production") {
          $this->addJS($key, $js_file);
        } else {
          $this->addJS($key, str_replace(".min", "",$js_file));
        }
      }
    }
    return $this;
  }

  /*
  * Add existing, minified css to header or create if does not already exist
  */
  private function combine_local_css() {
    if(ENVIRONMENT == "production") {
      if($this->redis_installed()) {
        $cached_css = ($this->redis->exists('simplemappr_hash')) ? array($this->redis->get('simplemappr_hash') . ".css") : array();
      } else {
        $cached_css = $this->files_cached(dirname(dirname(__FILE__)) . self::$css_cache_path, "css");
      }

      if(!$cached_css) {
        $css_min = '';
        foreach($this->local_css as $css_file) {
          $css_min .= CssMin::minify(file_get_contents($css_file)) . "\n";
        }
        $css_min_file = $this->hash . ".css";
        $handle = fopen(dirname(dirname(__FILE__)) . self::$css_cache_path . $css_min_file, 'x+');
        fwrite($handle, $css_min);
        fclose($handle);

        $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" media="screen,print" />');
      } else {
        foreach($cached_css as $css) {
          $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css . '" rel="stylesheet" media="screen,print" />');
        }
      }

    } else {
      foreach($this->local_css as $css_file) {
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
    $cache = $this->files_cached(dirname(dirname(__FILE__)) . self::$css_cache_path, "css");
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
    $namespace = (ENVIRONMENT == "production") ? "compiled" : "simplemappr";
    $header .= "head.js(";
    $headjs = array();
    foreach($this->js_header as $key => $file) {
      $headjs[] = "{".$key." : \"".$file."\"}";
    }
    $header .= join(",", $headjs);
    $header .= ");" . "\n";
    $header .= "head.ready(\"".$namespace."\", function () { SimpleMappr.init({ baseUrl : \"http://".$_SERVER['HTTP_HOST']."\", active : ".$session." }); });" . "\n";
    if($this->isAdministrator()) {
      $header .= "head.ready(\"admin\", function () { SimpleMapprAdmin.init(); });";
    }
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
  
  private function isAdministrator() {
    if(isset($_SESSION['simplemappr']) && User::$roles[$_SESSION['simplemappr']['role']] == 'administrator') {
      return true;
    }
    return false;
  }

  /*
  * Create Janrain inline javascript
  */
  private function getJanrain() {
    $locale = $this->getLocale();
    $locale_q = isset($_GET["locale"]) ? "?locale=" . $locale : "";
    $janrain  = "<script>" . "\n";
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