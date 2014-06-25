<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link      http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @package   SimpleMappr
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
 *
 */
namespace SimpleMappr;

class Header
{

    private $_js_header = array();
    private $_css_header = array();
    private $_hash = "";

    private static $_css_cache_path = "/public/stylesheets/cache/";
    private static $_js_cache_path = "/public/javascript/cache/";

    /**
     * An array of all javascript files to be minified
     */
    public $local_js_combined = array(
        'jquery'      => 'public/javascript/jquery-1.11.1.min.js',
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
        'jquery'    => '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js',
        'janrain'   => '//widget-cdn.rpxnow.com/js/lib/simplemappr/engage.js'
    );

    /**
     * An array of all css files to be minified
     */
    public $local_css = array(
        'public/stylesheets/raw/styles.css'
    );

    public static function flush_cache($output = true)
    {
        $cached_files = array();

        $css_files = array_diff(@scandir(dirname(__DIR__) . self::$_css_cache_path), array(".", "..", ".DS_Store"));
        foreach ($css_files as $file) {
            if (preg_match('/\.css$/i', $file)) {
                $cached_files[] = dirname(__DIR__) . self::$_css_cache_path . $file;
            }
        }
        $js_files = array_diff(@scandir(dirname(__DIR__) . self::$_js_cache_path), array(".", "..", ".DS_Store"));
        foreach ($js_files as $file) {
            if (preg_match('/\.js$/i', $file)) {
                $cached_files[] = dirname(__DIR__) . self::$_js_cache_path . $file;
            }
        }
        foreach ($cached_files as $file) {
            unlink($file);
        }

        $cloudflare_flush = "n/a";
        if (self::cloudflare_enabled()) {
            $cloudflare_flush = (self::flush_cloudflare()) ? true : false;
        }

        if ($output) {
            self::set_header("json");
            $response = array(
                "files" => true,
                "cloudflare" => $cloudflare_flush
            );
            echo json_encode($response);
        }
    }

    public static function flush_cloudflare()
    {
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $http_result = curl_exec($ch);
        $error = curl_error($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code == 200) {
            return true;
        }
        return false;
    }

    public static function cloudflare_enabled()
    {
        if (defined('CLOUDFLARE_KEY')) {
            return true;
        } else {
            return false;
        }
    }

    public static function set_header($mime = '')
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        switch($mime) {
        case '':
            break;

        case 'json':
            header("Content-Type: application/json");
            break;

        case 'html':
            header("Content-Type: text/html; charset=utf-8");
            break;

        case 'xml':
            header('Content-type: application/xml');
            break;

        case 'kml':
            header("Content-Type: application/vnd.google-earth.kml+xml kml; charset=utf8");
            break;

        case 'pptx':
            header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'docx':
            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'tif':
        case 'tiff':
            header("Content-Type: image/tiff");
            header("Content-Transfer-Encoding: binary");
            break;

        case 'svg':
            header("Content-Type: image/svg+xml");
            break;

        case 'jpg':
        case 'jpga':
            header("Content-Type: image/jpeg");
            break;

        case 'png':
        case 'pnga':
            header("Content-Type: image/png");
            break;

        default:
            header("Content-Type: image/png");
        }
    }

    function __construct()
    {
        $this->make_hash()
            ->add_remote_js()
            ->combine_local_js()
            ->combine_local_css();
    }

    /**
     * Obtain a file name in the cache directory
     *
     * @param string $dir The fully qualified directory
     * @param string $x The file extension
     * @return array An array of cached files
     */
    private function files_cached($dir, $x='js')
    {
        $allfiles = array_diff(@scandir($dir), array(".", "..", ".DS_Store"));
        $results = array();
        foreach ($allfiles as $file) {
            if (($x) ? preg_match('/\.'.$x.'$/i', $file) : 1) {
                $results[] = $file;
            }
        }
        return $results;
    }

    private function make_hash()
    {
        if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
            $this->_hash = substr(md5(microtime()), 0, 8);
        }
        return $this;
    }

    /**
     * Add javascript file(s) from remote CDN
     */
    private function add_remote_js()
    {
        if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
            $this->addJS('jquery', $this->remote_js['jquery']);
            $this->addJS('jquery_ui', $this->local_js_combined['jquery_ui']);
        }
        return $this;
    }

    /**
     * Add existing, minified javascript to header or create if does not already exist
     */
    private function combine_local_js()
    {
        if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
            $cached_js = $this->files_cached(dirname(__DIR__) . self::$_js_cache_path);

            if (!$cached_js) {
                unset($this->local_js_combined['jquery'], $this->local_js_combined['jquery_ui']);
                $js_contents = '';
                foreach ($this->local_js_combined as $js_file) {
                    $js_contents .= file_get_contents($js_file) . "\n";
                }

                $js_min_file = $this->_hash . ".js";
                $handle = fopen(dirname(__DIR__) . self::$_js_cache_path . $js_min_file, 'x+');
                fwrite($handle, $js_contents);
                fclose($handle);

                $this->addJS("compiled", self::$_js_cache_path . $js_min_file);
            } else {
                foreach ($cached_js as $js) {
                    $this->addJS("compiled", self::$_js_cache_path . $js);
                }
            }
        } else {
            foreach ($this->local_js_combined as $key => $js_file) {
                if ($key == "simplemappr") {
                    $js_file = str_replace(".min", "", $js_file);
                }
                $this->addJS($key, $js_file);
            }
        }
        if (!isset($_SESSION['simplemappr'])) {
            $this->addJS("janrain", $this->remote_js["janrain"]);
        }
        if ($this->isAdministrator()) {
            foreach ($this->admin_js as $key => $js_file) {
                if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
                    $this->addJS($key, $js_file);
                } else {
                    $this->addJS($key, str_replace(".min", "", $js_file));
                }
            }
        }
        return $this;
    }

    /**
     * Add existing, minified css to header or create if does not already exist
     */
    private function combine_local_css()
    {
        if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
            $cached_css = $this->files_cached(dirname(__DIR__) . self::$_css_cache_path, "css");

            if (!$cached_css) {
                $css_min = '';
                foreach ($this->local_css as $css_file) {
                    $css_min .= \CssMin::minify(file_get_contents($css_file)) . "\n";
                }
                $css_min_file = $this->_hash . ".css";
                $handle = fopen(dirname(__DIR__) . self::$_css_cache_path . $css_min_file, 'x+');
                fwrite($handle, $css_min);
                fclose($handle);

                $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" media="screen,print" />');
            } else {
                foreach ($cached_css as $css) {
                    $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css . '" rel="stylesheet" media="screen,print" />');
                }
            }

        } else {
            foreach ($this->local_css as $css_file) {
                $this->addCSS('<link type="text/css" href="' . $css_file . '" rel="stylesheet" media="screen,print" />');
            }
        }
        return $this;
    }

    /**
     * Add javascript file to array
     *
     * @param string $key Shorthand name for file
     * @param string $js Relative directory of file
     * @return void
     */
    private function addJS($key, $js)
    {
        $this->_js_header[$key] = $js;
    }

    /**
     * Add css file to array
     *
     * @param string $css The relative path of the css file
     * @return void
     */
    private function addCSS($css)
    {
        $this->_css_header[] = $css;
    }

    public function getHash()
    {
        $cache = $this->files_cached(dirname(__DIR__) . self::$_css_cache_path, "css");
        if ($cache) {
            list($hash, $extension) = explode(".", $cache[0]);
        } else {
            $hash = "1";
        }
        return $hash;
    }

    /**
     * Create the css header
     */
    public function getCSSHeader()
    {
        echo implode("\n", $this->_css_header) . "\n";
    }

    /**
     * DNS Prefetching
     */
    public function getDNSPrefetch()
    {
        $header  = "<link href=\"" . MAPPR_MAPS_URL . "\" rel=\"dns-prefetch\" />" . "\n";
        $header .= "<link href=\"" . MAPPR_MAPS_URL . "\" rel=\"prefetch\" />" . "\n";
        echo $header;
    }

    /**
     * Create the javascript header
     */
    public function getJSFooter()
    {
        $header  = "<script src=\"public/javascript/head.load.min.js\"></script>" . "\n";
        $header .= "<script>";
        $session = (isset($_SESSION['simplemappr'])) ? "\"true\"" : "\"false\"";
        $namespace = (ENVIRONMENT == "production" || ENVIRONMENT == "testing") ? "compiled" : "simplemappr";
        $header .= "head.js(";
        $headjs = array();
        foreach ($this->_js_header as $key => $file) {
            $headjs[] = "{".$key." : \"".$file."\"}";
        }
        $header .= join(",", $headjs);
        $header .= ");" . "\n";
        $header .= "head.ready(\"".$namespace."\", function () { SimpleMappr.init({ baseUrl : \"http://".$_SERVER['HTTP_HOST']."\", active : ".$session." }); });" . "\n";
        if ($this->isAdministrator()) {
            $header .= "head.ready(\"admin\", function () { SimpleMapprAdmin.init(); });";
        }
        $header .= "</script>" . "\n";
        echo $header;
    }

    public function getJSVars()
    {
        $foot = $this->getAnalytics();
        if (!isset($_SESSION['simplemappr'])) {
            $foot .= $this->getJanrain();
        }
        echo $foot;
    }
  
    private function isAdministrator()
    {
        if (isset($_SESSION['simplemappr']) && User::$roles[$_SESSION['simplemappr']['role']] == 'administrator') {
            return true;
        }
        return false;
    }

    /**
     * Create Janrain inline javascript
     *
     * @return string An HTML script tag snippet
     */
    private function getJanrain()
    {
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

    /**
     * Create Google Analytics inline javascript
     *
     * @return string An HTML script tag snippet
     */
    private function getAnalytics()
    {
        $analytics = "";
        if (ENVIRONMENT == "production" || ENVIRONMENT == "testing") {
            $analytics  = "<script>" . "\n";
            $analytics .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');" . "\n";
            $analytics .= "ga('create', '".GOOGLE_ANALYTICS."', '".MAPPR_DOMAIN."');" . "\n";
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
    private function getLocale()
    {
        return isset($_GET["locale"]) ? $_GET["locale"] : "en_US";
    }

}