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

require_once('jsmin.php');
require_once('cssmin.php');

class HEADER {

  private $js_header = array();
  private $css_header = array();

  /*
  * An array of all javascript files to be minified
  */
  public static $local_js_files = array(
    'public/javascript/jquery-1.6.4.min.js',
    'public/javascript/jquery-ui-1.8.16.min2.js',
    'public/javascript/jquery.colorpicker.min.js',
    'public/javascript/jquery.scrollTo.min.js',
    'public/javascript/jquery.Jcrop.min.js',
    'public/javascript/jquery.textarearesizer.compressed.js',
    'public/javascript/jquery.cookie.min.js',
    'public/javascript/jquery.download.min.js',
    'public/javascript/jquery.tipsy.min.js',
    'public/javascript/jquery.uitablefilter.min.js',
    'public/javascript/jquery.hotkeys.min.js',
    'public/javascript/jquery.tinycircleslider.min.js',
    'public/javascript/mapper.js'
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
        if ($value == 'public/javascript/jquery-1.6.4.min.js') {
          unset(self::$local_js_files[$key]);
        }
      }
      $this->addJS('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>');
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

        $this->addJS('<script type="text/javascript" src="public/javascript/cache/' . $js_min_file . '"></script>');
      } else {
        $this->addJS('<script type="text/javascript" src="public/javascript/cache/' . $cached_js . '"></script>');
      }
    } else {
      foreach(self::$local_js_files as $js_file) {
        $this->addJS('<script type="text/javascript" src="' . $js_file . '"></script>');
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

        $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $css_min_file . '" rel="stylesheet" />');
      } else {
        $this->addCSS('<link type="text/css" href="public/stylesheets/cache/' . $cached_css . '" rel="stylesheet" />');
      }

    } else {
      foreach(self::$local_css_files as $css_file) {
        $this->addCSS('<link type="text/css" href="' . $css_file . '" rel="stylesheet" />');
      }
    }
  }

  /*
  * Add javascript file to array
  * @param string $js
  */
  private function addJS($js) {
    $this->js_header[] = $js;
  }

  /*
  * Add css file to array
  * @param string $css
  */
  private function addCSS($css) {
    $this->css_header[] = $css;
  }

  /*
  * Create the javascript header
  */
  public function getJSHeader() {
    echo implode("\n", $this->js_header) . "\n";
  }

  /*
  * Create the css header
  */
  public function getCSSHeader() {
    echo implode("\n", $this->css_header) . "\n";
  }

  /*
  * Create Google Analytics inline javascript
  */
  public function getAnalytics() {
    $analytics = "";
    if(ENVIRONMENT == "production") {
      $analytics  = "<script type=\"text/javascript\">" . "\n";
      $analytics .= "var _gaq = _gaq || [];" . "\n";
      $analytics .= "_gaq.push(['_setAccount', '".GOOGLE_ANALYTICS."']);" . "\n";
      $analytics .= "_gaq.push(['_trackPageview']);
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();";
      $analytics .= "</script>" . "\n";
    }
    echo $analytics;
  }

}
?>