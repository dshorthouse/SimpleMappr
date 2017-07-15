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
 */
namespace SimpleMappr\Controller;

use SimpleMappr\Database;

/**
 * User model for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class User implements RestMethods
{
    /**
     * Database column upon which to sort
     *
     * @var string $sort
     */
    public $sort;

    /**
     * Direction to sort: asc or desc
     *
     * @var string $dir
     */
    public $dir;

    /**
     * Database query results
     *
     * @var object $results
     */
    public $results;

    /**
     * Hash stored in cookie
     *
     * @var string $_hash
     */
    private $_hash;

    /**
     * Database connection object
     *
     * @var object $_db
     */
    private $_db;

    /**
     * Defined roles to assigned to a user
     *
     * @var array $roles
     */
    public static $roles = [
        1 => 'user',
        2 => 'administrator'
    ];

    /**
     * Get user count
     *
     * @return integer count
     */
    public static function count()
    {
        $db = Database::getInstance();
        $sql = "SELECT count(*) as num FROM users";
        $db->prepare($sql);
        $count = $db->fetchFirstObject();
        return $count->num;
    }

    /**
     * Check permissions, used in router
     *
     * @param string $role The role
     *
     * @return bool True/False
     */
    public static function checkPermission($role = 'user')
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['simplemappr'])) {
            session_write_close();
            return false;
        }

        $user = (new User)->show_by_hash($_SESSION['simplemappr']['hash'])->results;
        if ($role == 'user' && (self::$roles[$user->role] == 'user' || self::$roles[$user->role] == 'administrator')) {
            session_write_close();
            return true;
        } elseif ($role == 'administrator' && self::$roles[$user->role] == 'administrator') {
            session_write_close();
            return true;
        } else {
            session_write_close();
            return false;
        }
    }

    /**
     * Determine if account belongs to an administrator
     *
     * @param object $user User object
     *
     * @return bool
     */
    public static function isAdministrator(User $user)
    {
        if (self::$roles[$user->results->role] == 'administrator') {
            return true;
        }
        return false;
    }

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->_db = Database::getInstance();
    }

    /**
     * Implemented index method
     *
     * @param object $params The parameters sent from the router
     *
     * @return object $this
     */
    public function index($params)
    {
        $this->sort = (array_key_exists('sort', $params)) ? $params['sort'] : "";
        $this->dir = (array_key_exists('dir', $params) && in_array(strtolower($params['dir']), ["asc", "desc"])) ? $params['dir'] : "desc";
        $order = "u.access {$this->dir}";

        if (!empty($this->sort)) {
            $order = "";
            if ($this->sort == "num" || $this->sort == "access" || $this->sort == "username") {
                if ($this->sort == "accessed") {
                    $order = "m.";
                }
                if ($this->sort == "username") {
                    $order = "u.";
                }
                $order = $order.$this->sort." ".$this->dir;
            }
        }

        $sql = "SELECT
                    u.uid, u.username, u.email, u.access, u.role, count(m.mid) as num
                FROM
                    users u
                LEFT JOIN
                    maps m ON (u.uid = m.uid)
                GROUP BY
                    u.uid
                HAVING count(m.mid) > 0
                ORDER BY " . $order;

        $this->_db->prepare($sql);
        $this->results = $this->_db->fetchAllObject();
        return $this;
    }

    /**
     * Implemented show method
     *
     * @param int $id The User identifier
     *
     * @return object $this
     */
    public function show($id)
    {
        $sql = "SELECT * FROM users u WHERE u.uid=:uid";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":uid", $id, 'integer');
        $this->results = $this->_db->fetchAllObject();
        return $this;
    }

    /**
     * Show a user by their hash
     *
     * @param string $hash The User hash
     *
     * @return object $this
     */
    public function show_by_hash($hash)
    {
        $sql = "SELECT * FROM users u WHERE u.hash=:hash";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":hash", $hash, 'string');
        $this->results = $this->_db->fetchFirstObject();
        return $this;
    }

    /**
     * Show a user by their identifier
     *
     * @param string $identifier The User identifier
     *
     * @return object $this
     */
    public function show_by_identifier($identifier)
    {
        $sql = "SELECT * FROM users u WHERE u.identifier=:identifier";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":identifier", $identifier, 'string');
        $this->results = $this->_db->fetchFirstObject();
        return $this;
    }

    /**
     * Implemented create method
     *
     * @param array $content The content to create
     *
     * @return array $content
     */
    public function create($content)
    {
        $content["uid"] = $this->_db->queryInsert('users', $content);
        return (object)$content;
    }

    /**
     * Implemented update method
     *
     * @param array  $content The array of content
     * @param string $where   The where clause
     *
     * @return object
     */
    public function update($content, $where)
    {
        $this->results = $this->_db->queryUpdate('users', $content, $where);
        return $this;
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The User identifier
     *
     * @return array status
     */
    public function destroy($id)
    {
        $sql = "DELETE
                    u, m
                FROM
                    users u
                LEFT JOIN
                    maps m ON u.uid = m.uid
                WHERE 
                    u.uid=:uid";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":uid", $id, 'integer');
        $this->_db->execute();

        return ["status" => "ok"];
    }
}
