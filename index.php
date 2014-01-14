<?php

/**************************************************************************

File: index.php

Description: Bootstrapper for SimpleMappr.

Developer: David P. Shorthouse
Email: davidpshorthouse@gmail.com

Copyright (C) 2010  David P. Shorthouse

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

**************************************************************************/

require_once(dirname(__FILE__) . '/config/conf.php');

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
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header('Content-Type: text/html; charset=utf-8');
        include_once("views/about.php");
        break;

      case "/api":
        $klass = $this->klass("mappr.api", "MapprApi");
        $this->setup_map($klass)->execute()->get_output();
        $this->log();
        break;

      case "/apidoc":
        include_once("views/apidoc.php");
        break;

      case "/application":
        $klass = $this->klass("mappr", "Mappr");
        $this->setup_map($klass)->execute()->get_output();
        break;

      case "/citation":
        $citation = $this->klass("citation", "Citation", $this->id);
        $citation->execute();
        break;

      case "/docx":
        $this->set_locale();
        $klass = $this->klass("mappr.docx", "MapprDocx");
        $this->setup_map($klass)->execute()->get_output();
        break;

      case "/feedback":
        include_once("views/feedback.php");
        break;
      
      case "/flush_cache":
        require_once('lib/header.class.php');
        require_once('lib/user.class.php');
        require_once('lib/session.class.php');
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
        $kml = $this->klass("kml", "Kml");
        $kml->get_request()->generate_kml();
        break;

      case "/logout":
        $this->klass("session", "Session", false);
        break;

      case "/map":
        $klass = $this->klass("mappr.map", "MapprMap", $this->id, $this->extension);
        $this->setup_map($klass)->execute()->get_output();
        break;

      case "/places":
        $this->klass("places", "Places", $this->id);
        break;

      case "/pptx":
        $this->set_locale();
        $klass = $this->klass("mappr.pptx", "MapprPptx");
        $this->setup_map($klass)->execute()->get_output();
        break;

      case "/query":
        $klass = $this->klass("mappr.query", "MapprQuery");
        $this->setup_map($klass)->execute()->query_layer()->get_output();
        break;

      case "/session":
        $this->klass("session", "Session", true);
        break;

      case "/user":
        $this->klass("user", "User", $this->id);
        break;

      case "/usermap":
        $this->klass("usermap", "Usermap", $this->id);
        break;
    
      case "/wfs":
        $klass = $this->klass("mappr.wfs", "MapprWfs");
        $this->setup_map($klass)->make_service()->execute()->get_output();
        break;

      case "/wms":
        $klass = $this->klass("mappr.wms", "MapprWms");
        $this->setup_map($klass)->make_service()->execute()->get_output();
        break;

      default:
        $this->render_404();
    }
  }

  private function klass($file, $klass, $param1 = "", $param2 = "") {
    require_once('lib/'.$file.'.class.php');
    return new $klass($param1, $param2);
  }

  private function setup_map($data) {
    $data->set_shape_path("lib/mapserver/maps")
         ->set_font_file("lib/mapserver/fonts/fonts.list")
         ->set_tmp_path(dirname(__FILE__)."/public/tmp/")
         ->set_tmp_url(MAPPR_MAPS_URL)
         ->set_default_projection("epsg:4326")
         ->set_max_extent("-180,-90,180,90")
         ->get_request();
    return $data;
  }

  private function log() {
    require_once('lib/logger.class.php');
    $logger = new LOGGER(dirname(__FILE__) . "/log/logger.log");
    $message = date('Y-m-d H:i:s') . " - $_SERVER[REMOTE_ADDR]";
    $logger->log($message);
  }

  private function set_locale() {
    require_once('lib/session.class.php');
    Session::select_locale();
  }

  private function set_up() {
    if(!isset($_SERVER['HTTP_HOST'])) { $this->render_404(); }

    $host = explode(".", $_SERVER['HTTP_HOST']);
    if(ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
      header('Location: http://' .  MAPPR_DOMAIN);
      exit();
    } else {
      require_once('lib/user.class.php');
      require_once('lib/session.class.php');
      require_once('lib/header.class.php');
      require_once('lib/mappr.class.php');

      Session::update_activity();

      return array(new Header, Session::$accepted_locales, User::$roles);
    }
  }

  private function partial($partial) {
    include_once("views/_".$partial.".php");
    call_user_func($partial);
  }

  private function render_404() {
    header("HTTP/1.0 404 Not Found");
    readfile(dirname(__FILE__).'/error/404.html');
    exit();
  }
}

$init = new Bootstrap;
?>