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
 * User handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class User extends Rest implements RestMethods
{
    public $sort;
    public $dir;
    public $results;

    private $_role;
    private $_db;

    public static $roles = array(
        1 => 'user',
        2 => 'administrator'
    );

    public static function check_permission()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION['simplemappr']) || self::$roles[$_SESSION['simplemappr']['role']] !== 'administrator') {
            Utilities::access_denied();
        }
    }

    function __construct($id)
    {
        session_start();
        if (!isset($_SESSION['simplemappr'])) {
            Utilities::access_denied();
        }
        Session::select_locale();
        $this->id = (int)$id;
        $this->_role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
        Header::set_header();
        $this->execute();
    }

    /**
     * Utility method
     */
    private function execute()
    {
        if (self::$roles[$this->_role] !== 'administrator') {
            Utilities::access_denied();
        } else {
            $this->_db = new Database();
            $this->restful_action();
        }
    }

    /**
     * Implemented index method
     */
    public function index()
    {
        $this->sort = (isset($_GET['sort'])) ? $_GET['sort'] : "";
        $this->dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
        $order = "u.access {$this->dir}";

        if (isset($_GET['sort'])) {
            $order = "";
            $sort = $_GET['sort'];
            if ($sort == "num" || $sort == "access" || $sort == "username") {
                if ($sort == "accessed") {
                    $order = "m.";
                }
                if ($sort == "username") {
                    $order = "u.";
                }
                $order = $order.$sort." ".$this->dir;
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
        $this->results = $this->_db->fetch_all_object();
    }

    /**
     * Implemented show method
     *
     * @param int $id The User identifier
     * @return void
     */
    public function show($id)
    {
        $this->not_implemented();
    }

    /**
     * Implemented create method
     */
    public function create()
    {
        $this->not_implemented();
    }

    /**
     * Implemented update method
     */
    public function update()
    {
        $this->not_implemented();
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The User identifier
     * @return void
     */
    public function destroy($id)
    {
        if (self::$roles[$this->_role] !== 'administrator') {
            Utilities::access_denied();
        } else {
            $sql = "DELETE
                        u, m
                    FROM
                        users u
                    LEFT JOIN
                        maps m ON u.uid = m.uid
                    WHERE 
                        u.uid=:uid";
            $this->_db->prepare($sql);
            $this->_db->bind_param(":uid", $id, 'integer');
            $this->_db->execute();

            header("Content-Type: application/json");
            echo json_encode(array("status" => "ok"));
        }
    }

}