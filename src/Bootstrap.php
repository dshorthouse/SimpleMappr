<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.5
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
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
 *
 */
namespace SimpleMappr;

use \Phroute\Phroute\Autoloader;
use \Phroute\Phroute\RouteCollector;
use \Phroute\Phroute\Dispatcher;

/**
 * Bootstrapper for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Bootstrap
{

    /**
     * Class constructor
     */
    function __construct()
    {
        $this->set_routes();
    }

    /**
     * Set the controller for each route
     *
     * @return views
     */
    private function set_routes()
    {
        $router = new RouteCollector();

        $router->filter('logAPI', function () { $this->log("API"); });
        $router->filter('logWMS', function () { $this->log("WMS"); });
        $router->filter('logWFS', function () { $this->log("WFS"); });
        $router->filter('check_role_user', function () { User::checkPermission('user'); });
        $router->filter('check_role_administrator', function () { User::checkPermission('administrator'); });

        $router->get('/', function () {
            return $this->main();
        });

        $router->get('/about', function () {
            Header::setHeader("html");
            Session::selectLocale();
            $citations = new Citation();
            $config = array(
                'citations' => $citations->index()
            );
            return $this->twig()->render("about.html", $config);
        });

        $router->any('/api', function () {
            $klass = $this->klass("MapprApi");
            return $this->setup_map($klass)->execute()->createOutput();
        }, array('after' => 'logAPI'));

        $router->get('/apidoc', function () {
            Session::selectLocale();
            array_walk(Mappr::$accepted_projections, function ($val, $key) use (&$projections) {
                $projections[] = $key . " (" . $val['name'] . ")";
            });
            $config = array(
                'mappr_maps_url' => MAPPR_MAPS_URL,
                'projections' => $projections
            );
            return $this->twig()->render("apidoc.html", $config);
        });

        $router->get('/apilog', function () {
            Header::setHeader('html');
            return $this->tail_log();
        }, array('before' => 'checkPermission'));

        $router->post('/application', function () {
            $klass = $this->klass("MapprApplication");
            return $this->setup_map($klass)->execute()->createOutput();
        });

        $router->post('/application.json', function () {
            Header::setHeader('json');
            $klass = $this->klass("MapprApplication");
            $output = $this->setup_map($klass)->execute()->createOutput();
            return json_encode($output);
        });

        $router->group(array('before' => 'check_role_administrator'), function ($router) {
            $router->get('/citation.json', function () {
                Header::setHeader("json");
                $klass = $this->klass("Citation");
                return json_encode($klass->index(null));
            })
            ->post('/citation', function () {
                Header::setHeader("json");
                $klass = $this->klass("Citation");
                return json_encode($klass->create((object)$_POST['citation']));
            })
            ->delete('/citation/{id:i}', function ($id) {
                Header::setHeader("json");
                $klass = $this->klass("Citation");
                return json_encode($klass->destroy($id));
            });
        });

        $router->post('/docx', function () {
            Session::selectLocale();
            $klass = $this->klass("MapprDocx");
            return $this->setup_map($klass)->execute()->createOutput();
        });

        $router->get('/feedback', function () {
            $locale = Session::selectLocale();
            $config = array(
                'locale' => $locale,
                'tweet' => ($locale['canonical'] == 'en') ? 'Tweet' : 'Tweeter'
            );
            return $this->twig()->render("feedback.html", $config);
        });

        $router->get('/flush_cache', function () {
            Header::flushCache();
        }, array('before' => 'check_role_administrator'));

        $router->get('/help', function () {
            $config = array(
                'locale' => Session::selectLocale()
            );
            return $this->twig()->render("help.html", $config);
        });

        $router->post('/kml', function () {
            $kml = $this->klass("Kml");
            return $kml->get_request()->createOutput();
        });

        $router->get('/logout', function () {
            $this->klass("Session", false);
        });

        $router->get('/map/{id:i}', function ($id) {
            $klass = $this->klass("MapprMap", $id, 'png');
            return $this->setup_map($klass)->execute()->createOutput();
        });

        $router->get('/map/{id:i}.{ext:[kml|svg|json]+}', function ($id, $ext) {
            $klass = $this->klass("MapprMap", $id, $ext);
            return $this->setup_map($klass)->execute()->createOutput();
        });

        $router->get('/places', function () {
            Header::setHeader("html");
            Session::selectLocale();
            $config = array(
                'rows' => $this->klass("Places")->index((object)$_GET)->results
            );
            return $this->twig()->render("fragments/fragment.places.html", $config);
        });

        $router->get('/places.json', function () {
            Header::setHeader("json");
            return json_encode($this->klass("Places")->index((object)$_GET)->results);
        });

        $router->post('/pptx', function () {
            Session::selectLocale();
            $klass = $this->klass("MapprPptx");
            return $this->setup_map($klass)->execute()->createOutput();
        });

        $router->post('/query', function () {
            Header::setHeader("json");
            $klass = $this->klass("MapprQuery");
            return json_encode($this->setup_map($klass)->execute()->query_layer()->data);
        });

        $router->post('/session', function () {
            $this->klass("Session", true);
        });

        $router->group(array('before' => 'check_role_user'), function ($router) {
            $router->get('/share', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->klass("Share")->index((object)$_GET);
                $config = array(
                    'rows' => $results->results,
                    'sort' => $results->sort,
                    'dir' => $results->dir
                );
                return $this->twig()->render("fragments/fragment.share.html", $config);
            })
            ->post('/share', function () {
                Header::setHeader('json');
                return json_encode($this->klass("Share")->create((object)$_POST));
            })
            ->delete('/share/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->klass("Share")->destroy($id));
            });
        });

        $router->group(array('before' => 'check_role_administrator'), function ($router) {
            $router->get('/user', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->klass("User")->index((object)$_GET);
                $config = array(
                    'rows' => $results->results,
                    'sort' => $results->sort,
                    'dir' => $results->dir
                );
                return $this->twig()->render("fragments/fragment.user.html", $config);
            })
            ->delete('/user/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->klass("User")->destroy($id));
            });
        });
        
        $router->group(array('before' => 'check_role_user'), function ($router) {
            $router->get('/usermap', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->klass("Usermap")->index((object)$_GET);
                $config = array(
                    'rows' => $results->results,
                    'total' => $results->total,
                    'sort' => $results->sort,
                    'dir' => $results->dir,
                    'filter_username' => $results->filter_username,
                    'filter_uid' => $results->filter_uid,
                    'row_count' => $results->row_count
                );
                return $this->twig()->render("fragments/fragment.usermap.html", $config);
            })
            ->get('/usermap/{id:i}.json', function ($id) {
                Header::setHeader('json');
                return json_encode($this->klass("Usermap")->show($id));
            })
            ->post('/usermap', function () {
                Header::setHeader('json');
                return json_encode($this->klass("Usermap")->create($_POST));
            })
            ->delete('/usermap/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->klass("Usermap")->destroy($id));
            });
        });

        $router->any('/wfs', function () {
            Header::setHeader("xml");
            $klass = $this->klass("MapprWfs");
            return $this->setup_map($klass)->makeService()->execute()->createOutput();
        }, array('after' => 'logWFS'));

        $router->any('/wms', function () {
            //Headers are set in WMS class
            $klass = $this->klass("MapprWms");
            return $this->setup_map($klass)->makeService()->execute()->createOutput();
        }, array('after' => 'logWMS'));

        try {
            $dispatcher = new Dispatcher($router->getData());
            $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            echo $response;
        } catch(\Exception $e) {
            echo $this->render_404();
        }

    }

    /**
     * Instantiates a new class and passes parameters.
     *
     * @param string $klass  The class name.
     * @param string $param1 First optional parameter.
     * @param string $param2 Second optional parameter.
     *
     * @return class $klass  The instance of class.
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
     *
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
     *
     * @return void
     */
    private function log($type)
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $ip = $_SERVER["REMOTE_ADDR"];
        if (defined("CLOUDFLARE_KEY") && ENVIRONMENT == "production") {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
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
                        $logs[$key] = str_replace($match, "<a href=\"https://who.is/whois-ip/ip-address/".$match[0]."\">".$match[0]."</a>", $log);
                    }
                }
            }
        }
        return ($logs) ? implode("<br>", $logs) : "No log data";
    }

    /**
     * Redirect requests or set-up sessions
     *
     * @return array instance of Header class, locales, roles
     */
    private function main()
    {
        $host = explode(".", $_SERVER['HTTP_HOST']);
        if (ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
            header('Location: http://'.MAPPR_DOMAIN);
            exit();
        } else {
            Session::updateActivity();
            return $this->twig()->render("main.html");
        }
    }

    /**
     * Load twig templating engine
     *
     * @return twig object
     */
    private function twig()
    {
        $loader = new \Twig_Loader_Filesystem(ROOT. "/views");
        $cache = (ENVIRONMENT == "development") ? false : ROOT . "/public/tmp";
        $reload = (ENVIRONMENT == "development") ? true : false;
        $twig = new \Twig_Environment($loader, array('cache' => $cache, 'auto_reload' => $reload));
        $twig->addExtension(new \Twig_Extensions_Extension_I18n());
        $twig->addGlobal('environment', ENVIRONMENT);

        $header = new Header;
        $locale = isset($_GET["locale"]) ? $_GET["locale"] : 'en_US';
        $qlocale = "?v=" . $header->getHash();
        $qlocale .= isset($_GET['locale']) ? "&locale=" . $_GET["locale"] : "";

        $twig->addGlobal('locales', Session::$accepted_locales);
        $twig->addGlobal('roles', User::$roles);
        $twig->addGlobal('projections', Mappr::$accepted_projections);
        $twig->addGlobal('og_url', 'http://' . $_SERVER['HTTP_HOST']);
        $twig->addGlobal('og_logo', 'http://' . $_SERVER['HTTP_HOST'] . '/public/images/logo_og.png');
        $twig->addGlobal('stylesheet', $header->getCSSHeader());
        $twig->addGlobal('session', (isset($_SESSION['simplemappr'])) ? $_SESSION['simplemappr'] : array());
        $twig->addGlobal('qlocale', $qlocale);
        $twig->addGlobal('locale', $locale);
        $twig->addGlobal('language', Session::$accepted_locales[$locale]['canonical']);
        $twig->addGlobal('footer', $header->getJSVars() . $header->getJSFooter());

        return $twig;
    }

    /**
     * Render a 404 document
     *
     * @return void
     */
    private function render_404()
    {
        http_response_code(404);
        $config = array(
            'title' => ' - Not Found',
            'google_analytics' => GOOGLE_ANALYTICS
        );
        return $this->twig()->render("404.html", $config);
    }
}