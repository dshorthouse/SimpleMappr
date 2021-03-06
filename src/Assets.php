<?php

/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */
namespace SimpleMappr;

use CssMin;
use SimpleMappr\Controller\User;

/**
 * Header handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2018 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Assets
{
    /**
     * Empty array to hold all js files for header
     *
     * @var array $_js_header
     */
    private $_js_header = [];

    /**
     * Empty array to hold css files for header
     *
     * @var array $_css_header
     */
    private $_css_header = [];

    /**
     * Truncated MD5 hash for compiled css and js files
     *
     * @var string $_hash
     */
    private $_hash = "";

    /**
     * Directory to put combined and minified css files
     *
     * @var string $_css_cache_path
     */
    private static $_css_cache_path = "/public/stylesheets/cache/";

    /**
     * Directory to put combined and minified js files
     *
     * @var string $_js_cache_path
     */
    private static $_js_cache_path = "/public/javascript/cache/";

    /**
     * Javascript files that remain uncombined
     *
     * @var array $local_js_uncombined
     */
    public $local_js_uncombined = [
        'jquery'      => 'public/javascript/jquery-3.2.1.min.js',
        'jquery_ui'   => 'public/javascript/jquery-ui-1.12.1.custom.min.js',
        'janrain'     => 'public/javascript/janrain.engage.min.js'
     ];

    /**
     * All js files to be combined
     *
     * @var array $local_js_combined
     */
    public $local_js_combined = [
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
        'parse'       => 'public/javascript/papaparse.min.js',
        'simplemappr' => 'public/javascript/simplemappr.min.js'
    ];

    /**
     * Array of all js files to be added in admin tab
     *
     * @var array $admin_js
     */
    public $admin_js = [
        'wysiwyg'  => 'public/javascript/trumbowyg.min.js',
        'scrollTo' => 'public/javascript/jquery.scrollTo.min.js',
        'admin'    => 'public/javascript/simplemappr.admin.min.js'
    ];

    /**
     * Array of remote js files to be swapped in production
     *
     * @var array $remote_js
     */
    public $remote_js = [
        'jquery'    => '//code.jquery.com/jquery-3.2.1.min.js'
    ];

    /**
     * Array of all css files to be minified
     *
     * @var array $local_css
     */
    public $local_css = [
        'public/stylesheets/raw/styles.css'
    ];

    /**
     * Array of all css files to be minified
     *
     * @var array $local_css
     */
    public $admin_css = [
        'public/stylesheets/raw/trumbowyg.css'
    ];

    /**
     * Active session
     *
     * @var boolean $_active_session
     */
    private $_active_session;

    /**
     * Active session
     *
     * @var object $_user
     */
    private $_user;

    /**
     * Flush the caches
     *
     * @param bool $output If output is required
     *
     * @return void
     */
    public static function flushCache($output = true)
    {
        $wildcard_css = dirname(__DIR__) . self::$_css_cache_path . "*.{css}";
        $wildcard_js = dirname(__DIR__) . self::$_js_cache_path . "*.{js}";
        foreach (glob($wildcard_css, GLOB_BRACE) as $file) {
            unlink($file);
        }
        foreach (glob($wildcard_js, GLOB_BRACE) as $file) {
            unlink($file);
        }

        $cloudflare_flush = "n/a";
        if (self::cloudflareEnabled()) {
            $cloudflare_flush = (self::flushCloudflare()) ? true : false;
        }

        if ($output) {
            Header::setHeader("json");
            $response = [
                "files" => true,
                "cloudflare" => $cloudflare_flush
            ];
            echo json_encode($response);
        }
    }

    /**
     * Flush CloudFlare caches
     *
     * @return bool
     */
    public static function flushCloudflare()
    {
        $URL = "https://www.cloudflare.com/api_json.html";

        $data = [
            "a" => "fpurge_ts",
            "z" => CLOUDFLARE_DOMAIN,
            "email" => CLOUDFLARE_EMAIL,
            "tkn" => CLOUDFLARE_KEY,
            "v" => 1
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_exec($ch);
        curl_error($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code == 200) {
            return true;
        }
        return false;
    }

    /**
     * Determine if CloudFlare is enabled
     *
     * @return bool
     */
    public static function cloudflareEnabled()
    {
        if (defined('CLOUDFLARE_KEY') && !empty(CLOUDFLARE_KEY)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->_active_session = (isset($_SESSION['simplemappr'])) ? true : false;
        $this->_getUser()
            ->_makeHash()
            ->_addRemoteJs()
            ->_addUncombinedJs()
            ->_addCombinedJs()
            ->_addCombinedCss();
    }

    /**
     * Get the hash created from existing file name
     *
     * @return string $hash
     */
    public function getHash()
    {
        $cache = $this->_filesCached(dirname(__DIR__) . self::$_css_cache_path, "css");
        if ($cache) {
            return explode(".", $cache[0])[0];
        } else {
            return "1";
        }
    }

    /**
     * Create the css header
     *
     * @return string
     */
    public function getCSSHeader()
    {
        return implode("\n", $this->_css_header);
    }

    /**
     * Create the javascript header
     *
     * @return string The header
     */
    public function getJSFooter()
    {
        $header  = "<script src=\"public/javascript/head.load.min.js\">";
        $header .= "</script>" . "\n";
        $header .= "<script>";
        $session = ($this->_active_session) ? "\"true\"" : "\"false\"";
        $namespace = (ENVIRONMENT == "development") ? "simplemappr" : "compiled";
        $header .= "head.js(";
        $headjs = [];
        foreach ($this->_js_header as $key => $file) {
            $headjs[] = "{".$key." : \"".$file."\"}";
        }
        $header .= join(",", $headjs);
        $header .= ");" . "\n";
        $header .= "head.ready(\"".$namespace."\", function () { ";
        $header .= "SimpleMappr.init(";
        $header .= "{ ";
        $header .= "baseUrl : \"".MAPPR_URL."\", ";
        $header .= "active : ".$session.", ";
        $header .= "maxTextareaCount : ".MAXNUMTEXTAREA;
        $header .= "}";
        $header .= ");";
        $header .= "});" . "\n";
        if ($this->_active_session) {
            if (User::isAdministrator($this->_user)) {
                $header .= "head.ready(\"admin\", function () {";
                $header .= "SimpleMapprAdmin.init();";
                $header .= "});";
            }
        }
        $header .= "</script>" . "\n";
        return $header;
    }

    /**
     * Get all the js files for the footer
     *
     * @return string $foot
     */
    public function getJSVars()
    {
        $foot = $this->_getAnalytics();
        if (!$this->_active_session) {
            $foot .= $this->_getJanrain();
        }
        return $foot;
    }

    /**
     * Obtain a file name in the cache directory
     *
     * @param string $dir The fully qualified directory
     * @param string $x   The file extension, default 'js'
     *
     * @return array An array of cached files
     */
    private function _filesCached($dir, $x='js')
    {
        $allfiles = array_diff(@scandir($dir), [".", "..", ".DS_Store"]);
        $results = [];
        foreach ($allfiles as $file) {
            if (($x) ? preg_match('/\.'.$x.'$/i', $file) : 1) {
                $results[] = $file;
            }
        }
        return $results;
    }

    /**
     * Get the user object from an active session
     *
     * @return object $this
     */
    private function _getUser()
    {
        if ($this->_active_session) {
            $this->_user = (new User)->showByHash($_SESSION['simplemappr']['hash']);
        }
        return $this;
    }

    /**
     * Make a truncated MD5 hash for minified js and css file names
     *
     * @return object $this
     */
    private function _makeHash()
    {
        if (ENVIRONMENT != "development") {
            $this->_hash = substr(md5(microtime()), 0, 8);
        }
        return $this;
    }

    /**
     * Add javascript file(s) from remote CDN
     *
     * @return object $this
     */
    private function _addRemoteJs()
    {
        if (ENVIRONMENT == "production") {
            unset($this->local_js_uncombined['jquery']);
            $this->_addJs('jquery', $this->remote_js['jquery']);
        }
        return $this;
    }

    /**
     * Add uncombined, local javascript files
     *
     * @return object $this
     */
    private function _addUncombinedJs()
    {
        foreach ($this->local_js_uncombined as $key => $js_file) {
            if ($key == "janrain" && $this->_active_session) {
                continue;
            }
            $this->_addJs($key, $js_file);
        }
        return $this;
    }

    /**
     * Add existing, minified javascript to header or create it
     *
     * @return object $this
     */
    private function _addCombinedJs()
    {
        if (ENVIRONMENT == "development") {
            foreach ($this->local_js_combined as $key => $js_file) {
                if ($key == "simplemappr") {
                    $js_file = str_replace(".min", "", $js_file);
                }
                $this->_addJs($key, $js_file);
            }
        } else {
            $cached_js = $this->_filesCached(dirname(__DIR__) . self::$_js_cache_path);

            if (!$cached_js) {
                $js_contents = "";
                foreach ($this->local_js_combined as $js_file) {
                    $js_contents .= file_get_contents($js_file) . "\n";
                }

                $js_min_file = $this->_hash . ".js";
                $handle = fopen(dirname(__DIR__) . self::$_js_cache_path . $js_min_file, 'x+');
                fwrite($handle, $js_contents);
                fclose($handle);

                $this->_addJs("compiled", self::$_js_cache_path . $js_min_file);
            } else {
                foreach ($cached_js as $js) {
                    $this->_addJs("compiled", self::$_js_cache_path . $js);
                }
            }
        }
        if ($this->_active_session) {
            if (User::isAdministrator($this->_user)) {
                foreach ($this->admin_js as $key => $js_file) {
                    if (ENVIRONMENT == "development") {
                        $this->_addJs($key, str_replace(".min", "", $js_file));
                    } else {
                        $this->_addJs($key, $js_file);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add existing, minified css to header or create if does not already exist
     *
     * @return object $this
     */
    private function _addCombinedCss()
    {
        if (ENVIRONMENT == "development") {
            foreach ($this->local_css as $css_file) {
                $this->_addCss('<link type="text/css" href="/' . $css_file . '" rel="stylesheet" media="screen,print" />');
            }
        } else {
            $cached_css = $this->_filesCached(dirname(__DIR__) . self::$_css_cache_path, "css");

            if (!$cached_css) {
                $css_min = "";
                foreach ($this->local_css as $css_file) {
                    $css_min .= CssMin::minify(file_get_contents($css_file)) . "\n";
                }
                $css_min_file = $this->_hash . ".css";
                $handle = fopen(dirname(__DIR__) . self::$_css_cache_path . $css_min_file, 'x+');
                fwrite($handle, $css_min);
                fclose($handle);

                $this->_addCss('<link type="text/css" href="/public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" media="screen,print" />');
            } else {
                foreach ($cached_css as $css) {
                    $this->_addCss('<link type="text/css" href="/public/stylesheets/cache/' . $css . '" rel="stylesheet" media="screen,print" />');
                }
            }
        }
        if ($this->_active_session) {
            if (User::isAdministrator($this->_user)) {
                foreach ($this->admin_css as $key => $css_file) {
                    $this->_addCss('<link type="text/css" href="/' . $css_file . '" rel="stylesheet" media="screen,print" />');
                }
            }
        }

        return $this;
    }

    /**
     * Add javascript file to array
     *
     * @param string $key Shorthand name for file
     * @param string $js  Relative directory of file
     *
     * @return void
     */
    private function _addJs($key, $js)
    {
        $this->_js_header[$key] = $js;
    }

    /**
     * Add css file to array
     *
     * @param string $css The relative path of the css file
     *
     * @return void
     */
    private function _addCss($css)
    {
        $this->_css_header[] = $css;
    }

    /**
     * Create Janrain inline javascript
     *
     * @return string An HTML script tag snippet
     */
    private function _getJanrain()
    {
        $locale = $this->_getLocale();
        $locale_q = isset($_GET["locale"]) ? "?locale=" . $locale : "";
        $janrain  = "<script>" . "\n";
        $janrain .= "(function(w,d) {
if (typeof w.janrain !== 'object') { w.janrain = {}; }
w.janrain.settings = {};
w.janrain.settings.language = '" . Session::$accepted_locales[$locale]['canonical'] . "';
w.janrain.settings.tokenUrl = '" . MAPPR_URL . "/session/" . $locale_q . "';
function isJanrainReady() { janrain.ready = true; };
if (d.addEventListener) { d.addEventListener(\"DOMContentLoaded\", isJanrainReady, false); }
else if (w.attachEvent) { w.attachEvent('onload', isJanrainReady); }
else if (w.onLoad) { w.onload = isJanrainReady; }
})(window,document);" . "\n";
        $janrain .= "</script>" . "\n";
        return $janrain;
    }

    /**
     * Create Google Analytics inline javascript
     *
     * @return string An HTML script tag snippet
     */
    private function _getAnalytics()
    {
        $analytics = "";
        if (ENVIRONMENT == "production") {
            $analytics  = "<script>" . "\n";
            $analytics .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');" . "\n";
            $analytics .= "ga('create', '".GOOGLE_ANALYTICS."', '".Utility::parsedURL()["host"]."');" . "\n";
            $analytics .= "ga('send', 'pageview');" . "\n";
            $analytics .= "</script>" . "\n";
        }
        return $analytics;
    }

    /**
     * Return the locale
     *
     * @return string The locale string
     */
    private function _getLocale()
    {
        return isset($_GET["locale"]) ? $_GET["locale"] : "en_US";
    }
}
