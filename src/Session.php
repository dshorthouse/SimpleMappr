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
 * Session handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class Session
{
    public static $accepted_locales = array(
        'en_US' => array(
            'canonical' => 'en',
            'locale' => 'en_US',
            'hreflang' => 'en-us',
            'native' => 'English',
            'code'   => 'en_US.UTF-8'),
        'fr_FR' => array(
            'canonical' => 'fr',
            'hreflang' => 'fr-fr',
            'locale' => 'fr_FR',
            'native' => 'Français',
            'code'   => 'fr_FR.UTF-8'),
        );

    public static $domain = "messages";

    private $_token;
    private $_locale;
    private $_locale_code;
    private $_auth_info = array();

    /**
     * Create a user's session
     */
    public static function set_session()
    {
        session_cache_limiter('nocache');
        session_start();
        session_regenerate_id();
    }

    /**
     * Close writing to user's session
     */
    public static function close_session()
    {
        session_write_close();
    }

    /**
     * Destroy a user's session and the simplemappr cookie
     */
    public static function destroy()
    {
        self::set_session();
        $locale = isset($_SESSION['simplemappr']) ? $_SESSION['simplemappr']['locale'] : null;
        session_unset();
        session_destroy();
        setcookie("simplemappr", "", time() - 3600, "/", MAPPR_DOMAIN);
        self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($locale));
    }

    /**
     * Update the access field for the active user
     */
    public static function update_activity()
    {
        if (isset($_REQUEST["locale"]) && !array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
            http_response_code(404);
            readfile($_SERVER["DOCUMENT_ROOT"].'/error/404.html');
            exit();
        }

        $cookie = isset($_COOKIE["simplemappr"]) ? (array)json_decode(stripslashes($_COOKIE["simplemappr"])) : array("locale" => "en_US");

        if (!isset($_REQUEST["locale"]) && $cookie["locale"] != "en_US") {
            self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($cookie["locale"]));
        } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] == "en_US") {
            if (isset($_COOKIE["simplemappr"])) {
                $cookie["locale"] = "en_US";
                setcookie("simplemappr", json_encode($cookie), COOKIE_TIMEOUT, "/", MAPPR_DOMAIN);
            }
            self::redirect("http://" . MAPPR_DOMAIN);
        } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] != "en_US") {
            $cookie["locale"] = $_REQUEST["locale"];
        } else {
        }

        self::select_locale();

        if (!isset($_COOKIE["simplemappr"])) {
            return;
        }

        self::write_session($cookie);

        $db = new Database();
        $db->query_update('users', array('access' => time()), 'uid='.$_SESSION["simplemappr"]["uid"]);
    }

    public static function redirect($url)
    {
        Header::set_header();
        http_response_code(303);
        header("Location: " . $url);
        exit();
    }

    public static function make_locale_param($locale = "")
    {
        $param = "";
        if ($locale && $locale != "en_US") {
            $param = "/?locale=" . $locale;
        }
        return $param;
    }

    public static function select_locale()
    {
        if (isset($_REQUEST["locale"]) && array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
            putenv('LC_ALL='.self::$accepted_locales[$_REQUEST["locale"]]['code']);
            setlocale(LC_ALL, self::$accepted_locales[$_REQUEST["locale"]]['code']);
            bindtextdomain(self::$domain, $_SERVER["DOCUMENT_ROOT"]."/i18n");
            bind_textdomain_codeset(self::$domain, 'UTF-8'); 
            textdomain(self::$domain);
            return self::$accepted_locales[$_REQUEST["locale"]];
        } else {
            putenv('LC_ALL='.self::$accepted_locales['en_US']['code']);
            setlocale(LC_ALL, self::$accepted_locales['en_US']['code']);
            bindtextdomain(self::$domain, $_SERVER["DOCUMENT_ROOT"]."/i18n");
            bind_textdomain_codeset(self::$domain, 'UTF-8'); 
            textdomain(self::$domain);
            return self::$accepted_locales['en_US'];
        }
    }

    /**
     * Write a new session.
     *
     * @param array $data Content for the session.
     */
    public static function write_session($data)
    {
        self::set_session();
        $_SESSION["simplemappr"] = $data;
        self::close_session();
        setcookie("simplemappr", json_encode($data), COOKIE_TIMEOUT, "/", MAPPR_DOMAIN);
    }

    function __construct($new_session)
    {
        if ($new_session) {
            $this->execute();
        } else {
            self::destroy();
        }
    }

    private function execute()
    {
        $this->get_locale()
            ->get_token()
            ->make_call()
            ->make_session();
    }

    private function get_locale()
    {
        $this->_locale = Utilities::load_param('locale', 'en_US');
        $this->_locale_code = (array_key_exists($this->_locale, self::$accepted_locales)) ? self::$accepted_locales[$this->_locale]['code'] : 'en_US.UTF-8';
        return $this;
    }

    private function get_token()
    {
        $this->_token = Utilities::load_param('token', null);
        if ($this->_token) {
            return $this;
        } else {
            self::redirect("http://" . MAPPR_DOMAIN);
        }
    }

    /**
     * Execute POST to Janrain (formerly RPXNOW) to obtain OpenID account information
     */
    private function make_call()
    {
        $post_data = array('token' => $this->_token, 'apiKey' => RPX_KEY, 'format' => 'json');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $raw_json = curl_exec($curl);
        if ($raw_json == false) {
            echo "\n".'Curl error: ' . curl_error($curl);
            echo "\n".'HTTP code: ' . curl_errno($curl);
        }
        curl_close($curl);

        $this->_auth_info = json_decode($raw_json, true);

        return $this;
    }

    /**
     * Create a session and set a cookie
     */
    private function make_session()
    {
        if (isset($this->_auth_info['stat']) && $this->_auth_info['stat'] == 'ok') {

            $profile = $this->_auth_info['profile'];

            $identifier  = $profile['identifier'];
            $email       = (isset($profile['email'])) ? Utilities::check_plain($profile['email']) : '';
            $username    = (isset($profile['preferredUsername'])) ? Utilities::check_plain($profile['preferredUsername']) : $email;
            $displayname = (isset($profile['displayName'])) ? Utilities::check_plain($profile['displayName']) : '';

            $user = array(
                'identifier'  => $identifier,
                'username'    => $username,
                'displayname' => $displayname,
                'email'       => $email
            );

            $db = new Database();

            $sql = "SELECT
                        u.uid,
                        u.identifier,
                        u.email,
                        u.username,
                        u.displayname,
                        u.role
                    FROM 
                        users u 
                    WHERE  
                        u.identifier = :identifier";

            $db->prepare($sql);
            $db->bind_param(":identifier", $identifier);
            $result = $db->fetch_first_object();

            $user['uid'] = (!$result) ? $db->query_insert('users', $user) : $result->uid;
            $user['locale'] = $this->_locale;
            $user['role'] = (!$result->role) ? 1 : $result->role;

            $db->query_update('users', array('email' => $email, 'displayname' => $displayname, 'access' => time()), "uid=".$user['uid']);

            self::write_session($user);
            self::redirect("http://" . MAPPR_DOMAIN . self::make_locale_param($user['locale']));

        } else {
            echo 'An error occured: ' . $this->_auth_info['err']['msg'];
            exit();
        }
    }

}