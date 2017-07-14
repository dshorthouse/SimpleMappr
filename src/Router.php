<?php
/**
 * SimpleMappr - create point maps for publications and presentations
 *
 * PHP Version >= 5.6
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
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
use \Symfony\Component\Yaml\Yaml;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extensions_Extension_I18n;

use SimpleMappr\Constants\AcceptedMarkerShapes;
use SimpleMappr\Constants\AcceptedProjections;
use SimpleMappr\Controller\Citation;
use SimpleMappr\Controller\User;
use SimpleMappr\Mappr\Mappr;

/**
 * Router for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Router
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
     * @return mixed
     */
    private function _setRoutes()
    {
        $router = new RouteCollector();

        $router->filter('logAPI', function () {
            $this->_log("API");
        });
        $router->filter('logWMS', function () {
            $this->_log("WMS");
        });
        $router->filter('logWFS', function () {
            $this->_log("WFS");
        });
        $router->filter('check_role_user', function () {
            if(!User::checkPermission('user')) {
                echo $this->_renderError(403);
                return false;
            };
        });
        $router->filter('check_role_administrator', function () {
            if(!User::checkPermission('administrator')) {
                echo $this->_renderError(403);
                return false;
            };
        });

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

        $router->options('/api', function () {
            http_response_code(204);
        });

        $router->any('/api', function () {
            //headers are set in MapprApi class
            $klass = $this->_klass("Mappr\Api");
            return $klass->execute()->createOutput();
        }, ['after' => 'logAPI']);

        $router->get('/apidoc', function () {
            Header::setHeader('html');
            Session::selectLocale();
            $config = ['swagger' => $this->_klass("Controller\OpenApi")->index()];
            return $this->_twig()->render("apidoc.html", $config);
        });

        $router->get('/apilog', function () {
            Header::setHeader('html');
            return $this->_tailLog();
        }, ['before' => 'checkPermission']);

        $router->post('/application', function () {
            //headers are set in MapprApplication class
            $klass = $this->_klass("Mappr\Application");
            return $klass->execute()->createOutput();
        });

        $router->post('/application.json', function () {
            //headers are set in MapprApplication class
            $klass = $this->_klass("Mappr\Application");
            return $klass->execute()->createOutput();
        });

        $router->get('/citation.rss', function () {
            Header::setHeader('xml');
            $klass = $this->_klass("Controller\CitationFeed");
            $feed = $klass->makeChannel()->addItems()->getFeed();
            return $feed;
        });

        $router->group(['before' => 'check_role_administrator'], function ($router) {
            $router->get('/citation.json', function () {
                Header::setHeader("json");
                $klass = $this->_klass("Controller\Citation");
                return json_encode($klass->index(null));
            })
            ->get('/citation/{id:i}.json', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\Citation")->show($id));
            })
            ->post('/citation', function () {
                Header::setHeader("json");
                $klass = $this->_klass("Controller\Citation");
                return json_encode($klass->create($_POST['citation']));
            })
            ->put('/citation/{id:i}', function ($id) {
                Header::setHeader("json");
                $klass = $this->_klass("Controller\Citation");
                $put = array();
                parse_str(file_get_contents('php://input'), $put);
                return json_encode($klass->update($put['citation'], "id=".$id));
            })
            ->delete('/citation/{id:i}', function ($id) {
                Header::setHeader("json");
                $klass = $this->_klass("Controller\Citation");
                return json_encode($klass->destroy($id));
            });
        });

        $router->post('/docx', function () {
            //headers are set in MapprDocx class
            Session::selectLocale();
            $klass = $this->_klass("Mappr\Docx");
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
            Assets::flushCache();
        }, ['before' => 'check_role_administrator']);

        $router->get('/help', function () {
            $config = [
                'locale' => Session::selectLocale()
            ];
            return $this->_twig()->render("help.html", $config);
        });

        $router->post('/kml', function () {
            $clean_filename = Utility::cleanFilename(Utility::loadParam('file_name', time()));
            $download_token = Utility::loadParam('download_token', md5(time()));
            setcookie("fileDownloadToken", $download_token, time()+3600, "/");
            Header::setHeader("kml", $clean_filename . ".kml");
            return $this->_klass("Controller\Kml")->create($_POST);
        });

        $router->get('/logout', function () {
            $this->_klass("Session", false);
        });

        $router->get('/map/{id:i}', function ($id) {
            Header::setHeader('png');
            $klass = $this->_klass("Mappr\Map", $id, 'png');
            return $klass->execute()->createOutput();
        });

        $router->get('/map/{id:i}.{ext:[kml|svg|json|png|jpg]+}', function ($id, $ext) {
            Header::setHeader($ext);
            $klass = $this->_klass("Mappr\Map", $id, $ext);
            return $klass->execute()->createOutput();
        });

        $router->get('/places', function () {
            Header::setHeader("html");
            Session::selectLocale();
            $config = [
                'rows' => $this->_klass("Controller\Place")->index($_GET)->results
            ];
            return $this->_twig()->render("fragments/fragment.places.html", $config);
        });

        $router->get('/places.json', function () {
            Header::setHeader("json");
            return json_encode($this->_klass("Controller\Place")->index($_GET)->results);
        });

        $router->post('/pptx', function () {
            //headers set in MapprPptx class
            Session::selectLocale();
            $klass = $this->_klass("Mappr\Pptx");
            return $klass->execute()->createOutput();
        });

        $router->post('/query', function () {
            Header::setHeader("json");
            $klass = $this->_klass("Mappr\Query");
            return json_encode($klass->execute()->queryLayer()->data);
        });

        $router->post('/session', function () {
            $this->_klass("Session", true);
        });

        $router->get('/swagger.json', function () {
            Header::setHeader("json");
            return json_encode($this->_klass("Controller\OpenApi")->index());
        });

        $router->group(['before' => 'check_role_user'], function ($router) {
            $router->get('/share', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("Controller\Share")->index($_GET);
                $config = [
                    'rows' => $results->results,
                    'sort' => $results->sort,
                    'dir'  => $results->dir
                ];
                return $this->_twig()->render("fragments/fragment.share.html", $config);
            })
            ->post('/share', function () {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\Share")->create($_POST));
            })
            ->delete('/share/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\Share")->destroy($id));
            });
        });

        $router->group(['before' => 'check_role_administrator'], function ($router) {
            $router->get('/user', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("Controller\User")->index($_GET);
                $config = [
                    'total' => User::count(),
                    'rows'  => $results->results,
                    'sort'  => $results->sort,
                    'dir'   => $results->dir
                ];
                return $this->_twig()->render("fragments/fragment.user.html", $config);
            })
            ->delete('/user/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\User")->destroy($id));
            });
        });

        $router->group(['before' => 'check_role_user'], function ($router) {
            $router->get('/usermap', function () {
                Header::setHeader('html');
                Session::selectLocale();
                $results = $this->_klass("Controller\Map")->index($_GET);
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
                return json_encode($this->_klass("Controller\Map")->show($id));
            })
            ->post('/usermap', function () {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\Map")->create($_POST));
            })
            ->delete('/usermap/{id:i}', function ($id) {
                Header::setHeader('json');
                return json_encode($this->_klass("Controller\Map")->destroy($id));
            });
        });

        $router->any('/wfs', function () {
            Header::setHeader("xml");
            $klass = $this->_klass("Mappr\Wfs");
            return $klass->makeService()->execute()->createOutput();
        }, ['after' => 'logWFS']);

        $router->any('/wms', function () {
            //headers are set in Mappr\Wms class
            $klass = $this->_klass("Mappr\Wms");
            return $klass->makeService()->execute()->createOutput();
        }, ['after' => 'logWMS']);

        try {
            $dispatcher = new Dispatcher($router->getData());
            $parsed_url = parse_url(str_replace(":", "%3A", $_SERVER['REQUEST_URI']), PHP_URL_PATH);
            $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $parsed_url);
            echo $response;
        } catch (\Exception $e) {
            echo $this->_renderError(404);
        }
    }

    /**
     * Instantiates a new class and passes parameters.
     *
     * @param string $klass  The class name.
     * @param array $params Splat array.
     *
     * @return object $klass  The instance of class.
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
     * @return string
     */
    private function _tailLog()
    {
        $logger = new Logger(ROOT."/log/logger.log");
        $logs = $logger->tail(20);
        if ($logs) {
            $capture = '/(?<ip>(?:\d{1,3}\.){3}\d{1,3}|(?:[a-z0-9]{4}\:){7}[a-z0-9]{4})(?:.*?)(?<url>\/[api|wms|wfs].*)$/';

            foreach ($logs as $key => $log) {
                $logs[$key] = preg_replace_callback($capture, function ($matches) {
                    if (isset($matches["ip"]) && isset($matches["url"])) {
                        $url = (strlen($matches["url"]) < 100) ? $matches["url"] : substr($matches["url"], 0, 100) . "...";
                        $string  = "<a href=\"https://who.is/whois-ip/ip-address/${matches['ip']}\" target=\"_blank\">${matches['ip']}</a>";
                        $string .= " - ";
                        $string .= "<a href=\"${matches['url']}\" target=\"_blank\">${url}</a>";
                        return $string;
                    }
                    return;
                }, $log);
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
        if (!isset($_SERVER['HTTP_HOST'])) {
            echo $this->_renderError(400);
        }

        $host = explode(".", $_SERVER['HTTP_HOST']);
        if (ENVIRONMENT == "production" && $host[0] !== "www" && !in_array("local", $host)) {
            header('Location: '.MAPPR_URL);
            exit();
        } else {
            Session::updateActivity();
            $shapes_file = file_get_contents(Mappr::$shapefile_config);
            $shapes = Yaml::parse($shapes_file)['environments'][ENVIRONMENT];
            unset($shapes['layers']['base'], $shapes['layers']['stateprovinces_polygon']);

            $config = [
                'og_url' => MAPPR_URL,
                'og_logo' => MAPPR_URL . '/public/images/logo_og.png',
                'layers' => array_combine(array_keys($shapes['layers']), array_column($shapes['layers'], 'name')),
                'labels' => array_combine(array_keys($shapes['labels']), array_column($shapes['labels'], 'name')),
                'projections' => AcceptedProjections::$projections,
                'marker_shapes' => AcceptedMarkerShapes::$shapes,
                'locales' => Session::$accepted_locales,
                'num_textarea' => MAXNUMTEXTAREA
            ];
            return $this->_twig(true)->render("main.html", $config);
        }
    }

    /**
     * Load twig templating engine
     *
     * @param boolean Indicate if page elements are to be included.
     *
     * @return object
     */
    private function _twig($include_page_elements = false)
    {
        $loader = new Twig_Loader_Filesystem(ROOT. "/views");
        $cache = (ENVIRONMENT == "development") ? false : ROOT . "/public/tmp";
        $reload = (ENVIRONMENT == "development") ? true : false;
        $twig = new Twig_Environment($loader, ['cache' => $cache, 'auto_reload' => $reload]);
        $twig->addExtension(new Twig_Extensions_Extension_I18n());
        $twig->addGlobal('environment', ENVIRONMENT);

        $locale = Utility::loadParam("locale", "en_US");
        $qlocale = "?locale={$locale}";

        $session = [];
        if (isset($_SESSION['simplemappr'])) {
            $session = (array)(new User)->show_by_hash($_SESSION['simplemappr']['hash'])->results;
        }
        $twig->addGlobal('session', $session);
        $twig->addGlobal('locale', $locale);
        $twig->addGlobal('qlocale', $qlocale);
        $twig->addGlobal('language', Session::$accepted_locales[$locale]['canonical']);
        $twig->addGlobal('roles', User::$roles);

        if ($include_page_elements) {
            $header = new Assets;
            $twig->addGlobal('stylesheet', $header->getCSSHeader());
            $twig->addGlobal('footer', $header->getJSVars() . $header->getJSFooter());
        }

        return $twig;
    }

    /**
     * Render an error document
     *
     * @param integer $code HTTP error code
     *
     * @return string
     */
    private function _renderError($code = 404)
    {
        Header::setHeader('html');
        http_response_code($code);
        $title = " - ";
        switch ($code) {
            case 400:
                $title .= "Bad Request";
                break;
            case 403:
                $title .= "Forbidden";
                break;
            case 404:
                $title .= "Not Found";
                break;
            default:
                $title .= "Error";
        }
        $config = [
            'title' => $title,
            'og_url' => MAPPR_URL,
            'google_analytics' => GOOGLE_ANALYTICS
        ];

        return $this->_twig(true)->render("{$code}.html", $config);
    }
}
