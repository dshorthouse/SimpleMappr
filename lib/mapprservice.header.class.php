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

class HEADER {
    
    private $js_header = array();
    private $css_header = array();
    
    public static $local_js_files = array(
        'public/javascript/raphael-min.js',
        'public/javascript/jquery-1.6.4.min.js',
        'public/javascript/jquery-ui-1.8.16.min.js',
        'public/javascript/jquery.colorpicker.min.js',
        'public/javascript/jquery.scrollTo.min.js',
        'public/javascript/jquery.Jcrop.min.js',
        'public/javascript/jquery.textarearesizer.compressed.js',
        'public/javascript/jquery.cookie.js',
        'public/javascript/jquery.download.js',
        'public/javascript/tipsy/javascripts/jquery.tipsy.js',
        'public/javascript/jquery.uitablefilter.min.js',
        'public/javascript/janrain.js'
    );
    
    public static $css_files = array(
        'public/stylesheets/screen.css',
        'public/stylesheets/colorpicker.css',
        'public/stylesheets/jquery.Jcrop.css',
        'public/stylesheets/smoothness/jquery-ui-1.8.16.css',
        'public/javascript/tipsy/stylesheets/tipsy.css'
    );
    
    function __construct() {
        $this->remote_js_files();
        $this->local_js_files();
        $this->css_files();
    }
    
    public static function rand_string() {
        return chr(rand(65,90));
    }
    
    private function js_cached($dir, $x='js') {
      $files = array_diff(@scandir($dir), array(".", "..", ".DS_Store"));
      foreach($files as $file) {
        if(($x) ? preg_match('/\.'.$x.'$/i', $file) : 1) return $file;
      }
      return false;
    }

    private function remote_js_files() {
      if(ENVIRONMENT == "production") {
        foreach(self::$local_js_files as $key => $value) {
          if ($value == 'public/javascript/jquery-1.6.3.min.js' || $value == 'public/javascript/jquery-ui-1.8.16.min.js ') unset(self::$local_js_files[$key]);
        }
        $this->addJS('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>');
        $this->addJS('<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>');
      }
    }
    
    private function local_js_files() {

      self::$local_js_files[] = (ENVIRONMENT == "production") ? 'public/javascript/mapper.min.js' : 'public/javascript/mapper.js';

      if(ENVIRONMENT == "production") {
        
        $cached_js =  $this->js_cached(MAPPR_DIRECTORY . "/public/javascript/cache/");

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

          $this->addJS('<script type="text/javascript" src="public/javascript/cache/' . $js_min_file . '?' . self::rand_string() . '"></script>');
        }
        else {
          $this->addJS('<script type="text/javascript" src="public/javascript/cache/' . $cached_js . '?' . self::rand_string() . '"></script>');
        }
      }
      else {
        foreach(self::$local_js_files as $js_file) {
          $this->addJS('<script type="text/javascript" src="' . $js_file . '?' . self::rand_string() . '"></script>');
        }
      }
    }
    
    private function css_files() {
      foreach(self::$css_files as $css_file) {
        $this->addCSS('<link type="text/css" href="' . $css_file . '?' . self::rand_string() . '" rel="stylesheet" />');
      }
    }
    
    private function addJS($js) {
      $this->js_header[] = $js;
    }
    
    private function addCSS($css) {
      $this->css_header[] = $css;
    }
    
    public function getJSHeader() {
      echo implode("\n", $this->js_header) . "\n";
    }
    
    public function getCSSHeader() {
      echo implode("\n", $this->css_header) . "\n";
    }

    public function getAnalytics() {
      $analytics = "";
      if(ENVIRONMENT == "production") {
        $analytics = '<script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript">
        try {
        var pageTracker = _gat._getTracker("'.GOOGLE_ANALYTICS.'");
        pageTracker._setDomainName(".simplemappr.net");
        pageTracker._trackPageview();
        } catch(err) {}</script>' . "\n"; 
      }
      echo $analytics;
    }
}
?>