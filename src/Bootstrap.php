<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
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
    public function __construct()
    {
        mb_internal_encoding("UTF-8");
        mb_http_output("UTF-8");

        //set the default timezone
        date_default_timezone_set("America/Toronto");
        
        $this->_setRoutes();
    }

    /**
     * Set the controller for each route
     *
     * @return views
     */
    private function _setRoutes()
    {
        $router = new RouteCollector();

        $router->filter('logAPI', function () { $this->_log("API"); });
        $router->filter('logWMS', function () { $this->_log("WMS"); });
        $router->filter('logWFS', function () { $this->_log("WFS"); });
        $router->filter('check_role_user', function () { User::checkPermission('user'); });
        $router->filter('check_role_administrator', function () { User::checkPermission('administrator'); });

        $router->get('/', function () {
            return $this->_main();
        });

        $router->get('/about', function () {
            Header::setHeader("html");
            Session::selectLocale();
            $citations = new Citation();
            $config = [
                'citations' => $citations->index()
            ];
            return $this->_twig()->render("about.html", $config);
        });

        $router->any('/api', function () {
            $klass = $this->_klass("MapprApi");
            return $klass->execute()->createOutput();
        }, ['after' => 'logAPI']);

        $router->get('/apidoc', function () {
            Session::selectLocale();
            array_walk(AcceptedProjections::$projections, function ($val, $key) use (&$projections) {
                $projections[] = $key . " (" . $val['name'] . ")";
            });
            $config = [
                'mappr_maps_url' => MAPPR_MAPS_URL,
                'projections' => $projections
            ];
            return $this->_twig()->render("apidoc.html", $config);
        });

        $router->get('/apilog', function () {
            Header::setHeader('html');
            return $this->_tailLog();
        }, ['before' => 'checkPermission']);

        $router->post('/application', function () {
            $klass = $this->_klass("MapprApplication");
            return $klass->execute()->createOutput();
        });

        $router->post('/application.json', function () {
            Header::setHeader('json');
            $klass = $this->_klass("MapprApplication");
            $output = $klass->execute()->createOutput();
            return json_encode($output);
        });

        $router->group(['before' => 'check_role_administrator'], function ($router) {
            $router->get('/citation.json', function () {
                Header::setHeader("json");
                $klass = $this->_klass("Citation");
                return json_encode($klass->index(null));
            })
            ->post('/citation', function () {
                Header::setHeader("json");
                $klass = $this->_klass("Citation");
                return json_encode($klass->create((object)$_POST['citation']));
            })
            ->delete('/citation/{id:i}', function ($id) {
                Header::setHeader("json");
                $klass = $this->_klass("Citation");
                return json_encode($klass->destroy($id));
            });
        });

        $router->post('/docx', function () {
            Session::selectLocale();
            $klass = $this->_klass("MapprDocx");
            return $klass->execute()->createOutput();
        });

        $router->get('/feedback', function () {
            $locale = Session::selectLocale();
            $config = [
                'locale' => $locale,
                'tweet' => ($locale['canonical'] == 'en') ? 'Tweet' : 'Tweeter'
            ];
            return $this->_twig()->render("feedback.html", $config);
        });

        $router->get('/flush_cache', function () {
            Header::flushCache();
        }, ['before' => 'check_role_administrator']);

        $router->get('/help', function () {
            $config = [
                'locale' => Session::selectLocale()
            ];
            return $this->_twig()->render("help.html", $config);
        });

        $router->post('/kml', function () {
            $kml = $this->_klass("Kml");
            return $kml->getRequest()->createOutput();
        });

        $router->get('/logout', function () {
            $this->_klass("Session", false);
        });

        $router->get('/map/{id:i}', function ($id) {
            $klass = $this->_klass("MapprMap", $id, 'png');
            return $klass->execute()->createOutput();
        });

        $router->get('/map/{id:i}.{ext:[kml|svg|json]+}', function ($id, $ext) {
            $klass = $this->_klass("MapprMap", $id, $ext);
            return $klass->execute()->createOutput();
        });

        $router->get('/places', function () {
            Header::setHeader("html");
            Session::selectLocale();
            $config = [
                'rows' => $this->_klass("Places")->index((object)$_GET)->results
            ];
            return $this->_twig()->render("fragments/fragment.places.html", $config);
        });

        $router->get('/places.json', function () {
            Header::setHeader("json");
            return json_encode($this->_klass("Places")->index((object)$_GET)->results);
        });

        $router->post('/pptx', function () {
            Session::selectLocale();
            $klass = $this->_klass("MapprPptx");
            return $klass->execute()->createOutput();
        });

        $router->post('/query', function () {
            Header::setHeader("json");
            $klass = $this->_klass("MapprQuery");
            return json_encode($klass->execute()->queryLayer()->data);
        });

        $router->post('/session', function () {
            $this->_klass("Session", true);
        });

        $router->group(['before' => 'check_role_user'], function ($router) {
            $router->get('/share', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("Share")->index((object)$_GET);
                $config = [
                    'rows' => $results->results,
                    'sort' => $results->sort,
                    'dir' => $results->dir
                ];
                return $this->_twig()->render("fragments/fragment.share.html", $config);
            })
            ->post('/share', function () {
                Header::setHeader('json');
                return json_encode($this->_klass("Share")->create((object)$_POST));
            })
            ->delete('/share/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Share")->destroy($id));
            });
        });

        $router->group(['before' => 'check_role_administrator'], function ($router) {
            $router->get('/user', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("User")->index((object)$_GET);
                $config = [
                    'rows' => $results->results,
                    'sort' => $results->sort,
                    'dir' => $results->dir
                ];
                return $this->_twig()->render("fragments/fragment.user.html", $config);
            })
            ->delete('/user/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("User")->destroy($id));
            });
        });
        
        $router->group(['before' => 'check_role_user'], function ($router) {
            $router->get('/usermap', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("Usermap")->index((object)$_GET);
                $config = [
                    'rows' => $results->results,
                    'total' => $results->total,
                    'sort' => $results->sort,
                    'dir' => $results->dir,
                    'filter_username' => $results->filter_username,
                    'filter_uid' => $results->filter_uid,
                    'row_count' => $results->row_count
                ];
                return $this->_twig()->render("fragments/fragment.usermap.html", $config);
            })
            ->get('/usermap/{id:i}.json', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Usermap")->show($id));
            })
            ->post('/usermap', function () {
                Header::setHeader('json');
                return json_encode($this->_klass("Usermap")->create($_POST));
            })
            ->delete('/usermap/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Usermap")->destroy($id));
            });
        });

        $router->any('/wfs', function () {
            Header::setHeader("xml");
            $klass = $this->_klass("MapprWfs");
            return $klass->makeService()->execute()->createOutput();
        }, ['after' => 'logWFS']);

        $router->any('/wms', function () {
            //Headers are set in WMS class
            $klass = $this->_klass("MapprWms");
            return $klass->makeService()->execute()->createOutput();
        }, ['after' => 'logWMS']);

        try {
            $dispatcher = new Dispatcher($router->getData());
            $parsed_url = parse_url(str_replace(":", "%3A", $_SERVER['REQUEST_URI']), PHP_URL_PATH);
            $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $parsed_url);
            echo $response;
        } catch(\Exception $e) {
            echo $this->_render404();
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
    private function _klass($klass, ...$params)
    {
        $class = __NAMESPACE__ . '\\' . $klass;
        return new $class(...$params);
    }

    /**
     * Write a timestamp and URI to the logger.
     *
     * @param string $type Type of log, defaults to "API"
     *
     * @return void
     */
    private function _log($type)
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $ip = $_SERVER["REMOTE_ADDR"];
        if (defined("CLOUDFLARE_KEY") && ENVIRONMENT == "production") {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $message = implode(" - ", [date('Y-m-d H:i:s'), $ip, $type, $_SERVER["REQUEST_URI"]]);
        $logger->write($message);
    }

    /**
     * Instantiate the Logger class and execute its tail method
     *
     * @return void
     */
    private function _tailLog()
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $logs = $logger->tail();
        if ($logs) {
            $ip4 = '/(?:\d{1,3}\.){3}\d{1,3}/';
            $ip6 = '/(?:[a-z0-9]{4}\:){7}[a-z0-9]{4}/';
            foreach ($logs as $key => $log) {
                if (preg_match($ip4, $log, $match) || preg_match($ip6, $log, $match)) {
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
    private function _main()
    {
        $host = explode(".", $_SERVER['HTTP_HOST']);
        if (ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
            header('Location: '.MAPPR_URL);
            exit();
        } else {
            Session::updateActivity();
            return $this->_twig()->render("main.html");
        }
    }

    /**
     * Load twig templating engine
     *
     * @return twig object
     */
    private function _twig()
    {
        $loader = new \Twig_Loader_Filesystem(ROOT. "/views");
        $cache = (ENVIRONMENT == "development") ? false : ROOT . "/public/tmp";
        $reload = (ENVIRONMENT == "development") ? true : false;
        $twig = new \Twig_Environment($loader, ['cache' => $cache, 'auto_reload' => $reload]);
        $twig->addExtension(new \Twig_Extensions_Extension_I18n());
        $twig->addGlobal('environment', ENVIRONMENT);

        $header = new Header;
        $locale = isset($_GET["locale"]) ? $_GET["locale"] : 'en_US';
        $qlocale = "?v=" . $header->getHash();
        $qlocale .= isset($_GET['locale']) ? "&locale=" . $_GET["locale"] : "";

        $twig->addGlobal('locales', Session::$accepted_locales);
        $twig->addGlobal('roles', User::$roles);
        $twig->addGlobal('projections', AcceptedProjections::$projections);
        $twig->addGlobal('marker_shapes', AcceptedMarkerShapes::$shapes);
        $twig->addGlobal('og_url', 'http://' . $_SERVER['HTTP_HOST']);
        $twig->addGlobal('og_logo', 'http://' . $_SERVER['HTTP_HOST'] . '/public/images/logo_og.png');
        $twig->addGlobal('stylesheet', $header->getCSSHeader());
        $twig->addGlobal('session', (isset($_SESSION['simplemappr'])) ? $_SESSION['simplemappr'] : []);
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
    private function _render404()
    {
        http_response_code(404);
        $config = [
            'title' => ' - Not Found',
            'google_analytics' => GOOGLE_ANALYTICS
        ];
        return $this->_twig()->render("404.html", $config);
    }
}