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

/**
 * Session handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
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
     *
     * @return void
     */
    public static function setSession()
    {
        session_cache_limiter('nocache');
        session_start();
        session_regenerate_id();
    }

    /**
     * Close writing to user's session
     *
     * @return void
     */
    public static function closeSession()
    {
        session_write_close();
    }

    /**
     * Destroy a user's session and the simplemappr cookie
     *
     * @return void
     */
    public static function destroy()
    {
        self::setSession();
        $locale = isset($_SESSION['simplemappr']) ? $_SESSION['simplemappr']['locale'] : null;
        session_unset();
        session_destroy();
        setcookie("simplemappr", "", time() - 3600, "/", Utilities::parsedURL()['host']);
        self::redirect(MAPPR_URL . self::makeLocaleParam($locale));
    }

    /**
     * Update the access field for the active user
     *
     * @return void
     */
    public static function updateActivity()
    {
        if (isset($_REQUEST["locale"]) && !array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
            http_response_code(404);
            readfile($_SERVER["DOCUMENT_ROOT"].'/error/404.html');
            exit();
        }

        $cookie = isset($_COOKIE["simplemappr"]) ? (array)json_decode(stripslashes($_COOKIE["simplemappr"])) : array("locale" => "en_US");

        if (!isset($_REQUEST["locale"]) && $cookie["locale"] != "en_US") {
            self::redirect(MAPPR_URL . self::makeLocaleParam($cookie["locale"]));
        } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] == "en_US") {
            if (isset($_COOKIE["simplemappr"])) {
                $cookie["locale"] = "en_US";
                setcookie("simplemappr", json_encode($cookie, JSON_UNESCAPED_UNICODE), COOKIE_TIMEOUT, "/", Utilities::parsedURL()['host']);
            }
            self::redirect(MAPPR_URL);
        } elseif (isset($_REQUEST["locale"]) && $_REQUEST["locale"] != "en_US") {
            $cookie["locale"] = $_REQUEST["locale"];
        } else {
        }

        self::selectLocale();

        if (!isset($_COOKIE["simplemappr"])) {
            return;
        }

        self::writeSession($cookie);

        $db = Database::getInstance();
        $db->queryUpdate('users', array('access' => time()), 'uid='.$_SESSION["simplemappr"]["uid"]);
    }

    /**
     * Redirect to a URL and set a 303 code
     *
     * @param string $url The destination URL
     *
     * @return void
     */
    public static function redirect($url)
    {
        Header::setHeader();
        http_response_code(303);
        header("Location: " . $url);
        exit();
    }

    /**
     * Add a locale parameter to the URL path
     *
     * @param string $locale The locale
     *
     * @return string the path
     */
    public static function makeLocaleParam($locale = "")
    {
        $param = "";
        if ($locale && $locale != "en_US") {
            $param = "/?locale=" . $locale;
        }
        return $param;
    }

    /**
     * Select the locale
     *
     * @return string The locale
     */
    public static function selectLocale()
    {
        if (isset($_REQUEST["locale"]) && array_key_exists($_REQUEST["locale"], self::$accepted_locales)) {
            putenv('LC_ALL='.self::$accepted_locales[$_REQUEST["locale"]]['code']);
            setlocale(LC_MESSAGES, self::$accepted_locales[$_REQUEST["locale"]]['code']);
            bindtextdomain(self::$domain, $_SERVER["DOCUMENT_ROOT"]."/i18n");
            bind_textdomain_codeset(self::$domain, 'UTF-8'); 
            textdomain(self::$domain);
            return self::$accepted_locales[$_REQUEST["locale"]];
        } else {
            putenv('LC_ALL='.self::$accepted_locales['en_US']['code']);
            setlocale(LC_MESSAGES, self::$accepted_locales['en_US']['code']);
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
     *
     * @return void
     */
    public static function writeSession($data)
    {
        self::setSession();
        $_SESSION["simplemappr"] = $data;
        self::closeSession();
        setcookie("simplemappr", json_encode($data, JSON_UNESCAPED_UNICODE), COOKIE_TIMEOUT, "/", Utilities::parsedURL()['host']);
    }

    /**
     * Constructor
     *
     * @param bool $new_session Create a new session or destroy one
     *
     * @return void
     */
    public function __construct($new_session)
    {
        if ($new_session) {
            $this->_execute();
        } else {
            self::destroy();
        }
    }

    /**
     * Executor method
     *
     * @return void
     */
    private function _execute()
    {
        $this->_getLocale()
            ->_getToken()
            ->_makeCall()
            ->_makeSession();
    }

    /**
     * Get the locale
     *
     * @return object $this
     */
    private function _getLocale()
    {
        $this->_locale = Utilities::loadParam('locale', 'en_US');
        $this->_locale_code = (array_key_exists($this->_locale, self::$accepted_locales)) ? self::$accepted_locales[$this->_locale]['code'] : 'en_US.UTF-8';
        return $this;
    }

    /**
     * The the token from the URL & if missing, redirect to homepage
     *
     * @return object $this
     */
    private function _getToken()
    {
        $this->_token = Utilities::loadParam('token', null);
        if ($this->_token) {
            return $this;
        } else {
            self::redirect(MAPPR_URL);
        }
    }

    /**
     * Execute POST to Janrain (formerly RPXNOW) to obtain OpenID account information
     *
     * @return object $this
     */
    private function _makeCall()
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
            echo "\nCurl error: " . curl_error($curl);
            echo "\nHTTP code: " . curl_errno($curl);
        }
        curl_close($curl);

        $this->_auth_info = json_decode($raw_json, true);

        return $this;
    }

    /**
     * Create a session and set a cookie
     *
     * @return void
     */
    private function _makeSession()
    {
        if (isset($this->_auth_info['stat']) && $this->_auth_info['stat'] == 'ok') {

            $profile = $this->_auth_info['profile'];

            $identifier  = $profile['identifier'];
            $email       = (isset($profile['email'])) ? Utilities::checkPlain($profile['email']) : "";
            $username    = (isset($profile['preferredUsername'])) ? Utilities::checkPlain($profile['preferredUsername']) : $email;
            $displayname = (isset($profile['displayName'])) ? Utilities::checkPlain($profile['displayName']) : "";

            $user = array(
                'identifier'  => $identifier,
                'username'    => $username,
                'displayname' => $displayname,
                'email'       => $email
            );

            $db = Database::getInstance();

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
            $db->bindParam(":identifier", $identifier);
            $result = $db->fetchFirstObject();

            $user['uid'] = (!$result) ? $db->queryInsert('users', $user) : $result->uid;
            $user['locale'] = $this->_locale;
            $user['role'] = (!$result->role) ? 1 : $result->role;

            $db->queryUpdate('users', array('email' => $email, 'displayname' => $displayname, 'access' => time()), "uid=".$user['uid']);

            self::writeSession($user);
            self::redirect(MAPPR_URL . self::makeLocaleParam($user['locale']));

        } else {
            echo 'An error occured: ' . $this->_auth_info['err']['msg'];
            exit();
        }
    }

}