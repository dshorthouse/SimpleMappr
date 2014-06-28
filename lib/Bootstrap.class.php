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

/**
 * Bootstrapper for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class Bootstrap
{

    private $_controller;
    private $_id;
    private $_extension;

    /**
     * Class constructor
     */
    function __construct()
    {
        $this->get_route()->set_controller();
    }

    /**
     * Set the controller, id, and extension variables for the request
     *
     * @return object $this
     */
    private function get_route()
    {
        $route = preg_split("/[\/.]+/", $_REQUEST['q']);
        $this->_controller = isset($route[0]) ? $route[0] : null;
        $this->_id = isset($route[1]) ? $route[1] : null;
        $this->_extension = isset($route[2]) ? $route[2] : null;
        return $this;
    }

    /**
     * Set the controller for each route
     *
     * @return void
     */
    private function set_controller()
    {
        switch ("/".$this->_controller) {
        case "/":
            $header = $this->set_up();
            header('Content-Type: text/html; charset=utf-8');
            include "views/main.php";
            break;

        case "/about":
            Header::set_header("html");
            include "views/about.php";
            break;

        case "/api":
            $klass = $this->klass("MapprApi");
            $this->setup_map($klass)->execute()->create_output();
            $this->log("API");
            break;

        case "/apidoc":
            include "views/apidoc.php";
            break;

        case "/apilog":
            $this->tail_log();
            break;

        case "/application":
            $klass = $this->klass("MapprApplication");
            $this->setup_map($klass)->execute()->create_output();
            break;

        case "/citation":
            $citation = $this->klass("Citation", $this->_id);
            $citation->execute();
            break;

        case "/docx":
            Session::select_locale();
            $klass = $this->klass("MapprDocx");
            $this->setup_map($klass)->execute()->create_output();
            break;

        case "/feedback":
            include "views/feedback.php";
            break;

        case "/flush_cache":
            Session::set_session();
            if (!isset($_SESSION["simplemappr"]) || User::$roles[$_SESSION["simplemappr"]["role"]] !== 'administrator') {
                http_response_code(404);
                readfile(dirname(__FILE__).'/error/404.html');
                exit();
            }
            Header::flush_cache();
            break;

        case "/help":
            include "views/help.php";
            break;

        case "/kml":
            $kml = $this->klass("Kml");
            $kml->get_request()->create_output();
            break;

        case "/logout":
            $this->klass("Session", false);
            break;

        case "/map":
            $klass = $this->klass("MapprMap", $this->_id, $this->_extension);
            $this->setup_map($klass)->execute()->create_output();
            break;

        case "/places":
            $this->klass("Places", $this->_id);
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
            $this->klass("User", $this->_id);
            break;

        case "/usermap":
            $this->klass("Usermap", $this->_id);
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

    /**
     * Instantiates a new class and passes parameters.
     *
     * @param string $klass The class name.
     * @param string $param1 First optional parameter.
     * @param string $param2 Second optional parameter.
     * @return new instance of $klass
     */
    private function klass($klass, $param1 = "", $param2 = "")
    {
        $class = __NAMESPACE__ . '\\' . $klass;
        return new $class($param1, $param2);
    }

    /**
     * Shortcut function for Mappr class methods.
     *
     * @param object $data Instance of a Mappr class.
     * @return object $data Loaded instance of a Mappr class.
     */
    private function setup_map($data)
    {
        return $data->set_shape_path(ROOT."/mapserver/maps")
            ->set_font_file(ROOT."/mapserver/fonts/fonts.list")
            ->set_tmp_path(ROOT."/public/tmp/")
            ->set_tmp_url(MAPPR_MAPS_URL)
            ->set_default_projection("epsg:4326")
            ->set_max_extent("-180,-90,180,90")
            ->get_request();
    }

    /**
     * Write a timestamp and URI to the logger.
     *
     * @param string $type Type of log, defaults to "API"
     * @return void
     */
    private function log($type = "API")
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $ip = (defined("CLOUDFLARE_KEY") && ENVIRONMENT == "production") ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];
        $message = implode(" - ", array(date('Y-m-d H:i:s'), $ip, $type, $_SERVER["REQUEST_URI"]));
        $logger->write($message);
    }

    /**
     * Instantiate the Logger class and execute its tail method
     *
     * @return void
     */
    private function tail_log()
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $logs = $logger->tail();
        if ($logs) {
            foreach ($logs as $key => $log) {
                if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $log, $match)) {
                    if (filter_var($match[0], FILTER_VALIDATE_IP)) {
                        $logs[$key] = str_replace($match, "<a href=\"http://tools.whois.net/whoisbyip/?host=".$match[0]."\" target=\"_blank\">".$match[0]."</a>", $log);
                    }
                }
            }
        }
        echo ($logs) ? implode("<br>", $logs) : "No log data";
    }

    /**
     * Redirect requests or set-up sessions
     *
     * @return array instance of Header class, locales, roles
     */
    private function set_up()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $this->render_404();
        }
        $host = explode(".", $_SERVER['HTTP_HOST']);
        if (ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
            header('Location: http://'.MAPPR_DOMAIN);
            exit();
        } else {
            Session::update_activity();
            return array(new Header, Session::$accepted_locales, User::$roles);
        }
    }

    /**
     * Include partial HTML
     *
     * @param string $partial Name of function called to include an HTML snippet
     * @return void
     */
    private function partial($partial)
    {
        include "views/_$partial.php";
        call_user_func($partial);
    }

    /**
     * Render a 404 document
     *
     * @return void
     */
    private function render_404()
    {
        http_response_code(404);
        readfile(ROOT.'/error/404.html');
        exit();
    }
}