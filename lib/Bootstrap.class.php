<?php

/********************************************************************

Bootstrap.class.php released under MIT License
Bootstrapper for SimpleMappr

Author: David P. Shorthouse <davidpshorthouse@gmail.com>
http://github.com/dshorthouse/SimpleMappr
Copyright (C) 2013 David P. Shorthouse {{{

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

namespace SimpleMappr;

class Bootstrap {

  private $controller;
  private $id;
  private $extension;

  function __construct() {
    $this->get_route()->set_controller();
  }

  private function get_route() {
    $route = preg_split("/[\/.]+/", $_REQUEST['q']);
    $this->controller = isset($route[0]) ? $route[0] : NULL;
    $this->id = isset($route[1]) ? $route[1] : NULL;
    $this->extension = isset($route[2]) ? $route[2] : NULL;
    return $this;
  }

  private function set_controller() {
    switch ("/".$this->controller) {
      case "/":
        $header = $this->set_up();
        header('Content-Type: text/html; charset=utf-8');
        include_once("views/main.php");
        break;

      case "/about":
        Header::set_header("html");
        include_once("views/about.php");
        break;

      case "/api":
        $klass = $this->klass("MapprApi");
        $this->setup_map($klass)->execute()->create_output();
        $this->log("API");
        break;

      case "/apidoc":
        include_once("views/apidoc.php");
        break;

      case "/apilog":
        $this->tail_log();
        break;

      case "/application":
        $klass = $this->klass("MapprApplication");
        $this->setup_map($klass)->execute()->create_output();
        break;

      case "/citation":
        $citation = $this->klass("Citation", $this->id);
        $citation->execute();
        break;

      case "/docx":
        Session::select_locale();
        $klass = $this->klass("MapprDocx");
        $this->setup_map($klass)->execute()->create_output();
        break;

      case "/feedback":
        include_once("views/feedback.php");
        break;
      
      case "/flush_cache":
        Session::set_session();
        if(!isset($_SESSION["simplemappr"]) || User::$roles[$_SESSION["simplemappr"]["role"]] !== 'administrator') {
          header("HTTP/1.0 404 Not Found");
          readfile(dirname(__FILE__).'/error/404.html');
          exit();
        }
        Header::flush_cache();
        break;

      case "/help":
        include_once("views/help.php");
        break;

      case "/kml":
        $kml = $this->klass("Kml");
        $kml->get_request()->create_output();
        break;

      case "/logout":
        $this->klass("Session", false);
        break;

      case "/map":
        $klass = $this->klass("MapprMap", $this->id, $this->extension);
        $this->setup_map($klass)->execute()->create_output();
        break;

      case "/places":
        $this->klass("Places", $this->id);
        break;

      case "/pptx":
        Session::select_locale();
        $klass = $this->klass("MapprPptx");
        $this->setup_map($klass)->execute()->create_output();
        break;

      case "/query":
        $klass = $this->klass("MapprQuery");
        $this->setup_map($klass)->execute()->query_layer()->create_output();
        break;

      case "/session":
        $this->klass("Session", true);
        break;

      case "/user":
        $this->klass("User", $this->id);
        break;

      case "/usermap":
        $this->klass("Usermap", $this->id);
        break;

      case "/wfs":
        $klass = $this->klass("MapprWfs");
        $this->setup_map($klass)->make_service()->execute()->create_output();
        $this->log("WFS");
        break;

      case "/wms":
        $klass = $this->klass("MapprWms");
        $this->setup_map($klass)->make_service()->execute()->create_output();
        $this->log("WMS");
        break;

      default:
        $this->render_404();
    }
  }

  private function klass($klass, $param1 = "", $param2 = "") {
    $class = __NAMESPACE__ . '\\' . $klass;
    return new $class($param1, $param2);
  }

  private function setup_map($data) {
    return $data->set_shape_path(ROOT."/lib/mapserver/maps")
         ->set_font_file(ROOT."/lib/mapserver/fonts/fonts.list")
         ->set_tmp_path(ROOT."/public/tmp/")
         ->set_tmp_url(MAPPR_MAPS_URL)
         ->set_default_projection("epsg:4326")
         ->set_max_extent("-180,-90,180,90")
         ->get_request();
  }

  private function log($type = "API") {
    $logger = new Logger(ROOT."/log/logger.log");
    $ip = (defined("CLOUDFLARE_KEY") && ENVIRONMENT == "production") ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];
    $message = implode(" - ", array(date('Y-m-d H:i:s'), $ip, $type, $_SERVER["REQUEST_URI"]));
    $logger->write($message);
  }

  private function tail_log() {
    $logger = new Logger(ROOT."/log/logger.log");
    echo ($logger->tail()) ? implode("<br>", $logger->tail()) : "No log data";
  }

  private function set_up() {
    if(!isset($_SERVER['HTTP_HOST'])) { $this->render_404(); }
    $host = explode(".", $_SERVER['HTTP_HOST']);
    if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
      header('Location: http://'.MAPPR_DOMAIN);
      exit();
    } else {
      Session::update_activity();
      return array(new Header, Session::$accepted_locales, User::$roles);
    }
  }

  private function partial($partial) {
    include "views/_$partial.php";
    call_user_func($partial);
  }

  private function render_404() {
    http_response_code(404);
    readfile(ROOT.'/error/404.html');
    exit();
  }
}