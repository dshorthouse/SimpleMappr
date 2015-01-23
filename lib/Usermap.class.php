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
 * User map handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class Usermap extends Rest implements RestMethods
{
    public $total;
    public $filter_username;
    public $filter_uid;
    public $results;
    public $dir;
    public $sort;
    public $row_count;

    private $_uid;
    private $_role;
    private $_db;

    /**
     * Class constructor
     *
     * @param int $id The map identifier
     */
    function __construct($id)
    {
        session_start();
        if (!isset($_SESSION['simplemappr'])) {
            Utilities::access_denied();
        }
        Session::select_locale();
        $this->id = (int)$id;
        $this->_uid = (int)$_SESSION['simplemappr']['uid'];
        $this->_role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
        $this->filter_uid = isset($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : null;
        Header::set_header();
        $this->execute();
    }

    /**
     * Utility method
     */
    private function execute()
    {
        $this->_db = new Database();
        $this->restful_action();
    }

    /**
     * Implemented index method
     */
    public function index()
    {
        $this->dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
        $this->sort = (isset($_GET['sort'])) ? $_GET['sort'] : "";

        $sql = "SELECT
                    u.username, COUNT(m.mid) AS total
                FROM
                    maps m
                INNER JOIN
                    users u ON (m.uid = u.uid)";
        $where = array();
        $limit = "";
        if (User::$roles[$this->_role] !== 'administrator') {
            $sql .=  " WHERE m.uid = :uid";
            $where['user'] = " WHERE m.uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bind_param(":uid", $this->_uid);
        } else {
            if ($this->filter_uid) {
                $sql .= " WHERE m.uid = :uid_q";
                $where['user'] = " WHERE m.uid = :uid_q";
                $this->_db->prepare($sql);
                $this->_db->bind_param(":uid_q", $this->filter_uid);        
            } else {
                $limit = " LIMIT 100";
                $this->_db->prepare($sql);
            }
        }

        $this->total = $this->_db->fetch_first_object()->total;
        $this->filter_username = $this->_db->fetch_first_object()->username;

        $order = "m.created {$this->dir}";

        $b = "";
        if (isset($_GET['search'])) {
            if (User::$roles[$this->_role] == 'administrator' && !$this->filter_uid) {
                $b = " WHERE ";
            }
            $where['where'] = $b."LOWER(m.title) LIKE :search";
            if (User::$roles[$this->_role] == 'administrator' && !$this->filter_uid) {
                $where['where'] .= " OR LOWER(u.username) LIKE :search";
            }
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == "created" || $_GET['sort'] == "updated") {
                $order = "m.".$_GET['sort'] . " {$this->dir}";
            }
        }

        $sql = "SELECT
                    m.mid,
                    m.title,
                    m.created,
                    m.updated,
                    u.uid,
                    u.username,
                    s.sid 
                FROM 
                    maps m 
                INNER JOIN
                    users u ON (m.uid = u.uid)
                LEFT JOIN
                    shares s ON (m.mid = s.mid)
                " . implode(" AND ", $where) . "
                ORDER BY " . $order . $limit;

        $this->_db->prepare($sql);
        if (User::$roles[$this->_role] !== 'administrator') {
            $this->_db->bind_param(":uid", $this->_uid, 'integer');
        } else {
            if ($this->filter_uid) {
                $this->_db->bind_param(":uid_q", $this->filter_uid, 'integer');
            }
        }
        if (isset($_GET['search'])) {
            $this->_db->bind_param(":search", "%{$_GET['search']}%", 'string');
        }
        $this->results = $this->_db->fetch_all_object();
        $this->row_count = $this->_db->row_count();
    }

    /**
     * Implemented show method
     *
     * @param int $id The map identifier.
     * @return void
     */
    public function show($id)
    {
        $sql = "
            SELECT
                mid, map
            FROM 
                maps
            WHERE
                mid = :mid";

        $this->_db->prepare($sql);
        $this->_db->bind_param(":mid", $id, 'integer');

        $record = $this->_db->fetch_first_object();
        $data['mid'] = ($record) ? $record->mid : "";
        $data['map'] = ($record) ? json_decode($record->map, true) : "";
        $data['status'] = ($data['map']) ? 'ok' : 'failed';

        Header::set_header('json');
        echo json_encode($data);
    }

    /**
     * Implemented create method
     */
    public function create()
    {
        $data = array(
            'uid' => $this->_uid,
            'title' => $_POST['save']['title'],
            'map' => json_encode($_POST),
            'created' => time(),
            'updated' => time()
        );

        //see if user's map by same title already exists
        $sql = "SELECT
                    mid
                FROM
                    maps
                WHERE
                    uid = :uid AND title = :title";
        $this->_db->prepare($sql);
        $this->_db->bind_param(":uid", $this->_uid, 'integer');
        $this->_db->bind_param(":title", $data['title'], 'string');
        $record = $this->_db->fetch_first_object($sql);

        $output = array();
        $output['status'] = "ok";

        if ($record) {
            unset($data['created']);
            $this->_db->query_update('maps', $data, 'mid='.$record->mid);
            $output['mid'] = $record->mid;
        } else {
            $output['mid'] = $this->_db->query_insert('maps', $data);
        }

        Header::set_header('json');
        echo json_encode($output);
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
     * @param int $id The map identifier.
     * @return void
     */
    public function destroy($id)
    {
        $sql = "DELETE 
                FROM
                    maps
                WHERE 
                    mid = :mid";
        if (User::$roles[$this->_role] == 'administrator') {
            $this->_db->prepare($sql);
            $this->_db->bind_param(":mid", $id, 'integer');
        } else {
            $sql .= " AND uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bind_param(":mid", $id, 'integer');
            $this->_db->bind_param(":uid", $this->_uid, 'integer');
        }
        $this->_db->execute();
        Header::set_header('json');
        echo json_encode(array("status" => "ok"));
    }

}