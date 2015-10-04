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

/**
 * User map handler for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Usermap implements RestMethods
{
    public $total;
    public $filter_username;
    public $filter_uid;
    public $results;
    public $dir;
    public $sort;
    public $search;
    public $row_count;

    private $_uid;
    private $_role;
    private $_db;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_uid = (int)$_SESSION['simplemappr']['uid'];
        $this->_role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
        $this->_db = new Database();
    }

    /**
     * Implemented index method
     *
     * @param object $params The parameters from the router
     *
     * @return object $this
     */
    public function index($params)
    {
        $this->dir = (property_exists($params, 'dir') && in_array(strtolower($params->dir), array("asc", "desc"))) ? $params->dir : "desc";
        $this->sort = (property_exists($params, 'sort')) ? $params->sort : "";
        $this->search = (property_exists($params, 'search')) ? $params->search : "";
        $this->filter_uid = (property_exists($params, 'uid')) ? (int)$params->uid : null;

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
            $this->_db->bindParam(":uid", $this->_uid);
        } else {
            if ($this->filter_uid) {
                $sql .= " WHERE m.uid = :uid_q";
                $where['user'] = " WHERE m.uid = :uid_q";
                $this->_db->prepare($sql);
                $this->_db->bindParam(":uid_q", $this->filter_uid);        
            } else {
                $limit = " LIMIT 100";
                $this->_db->prepare($sql);
            }
        }

        $this->total = $this->_db->fetchFirstObject()->total;
        $this->filter_username = $this->_db->fetchFirstObject()->username;

        $order = "m.created {$this->dir}";

        $b = "";
        if (!empty($this->search)) {
            if (User::$roles[$this->_role] == 'administrator' && !$this->filter_uid) {
                $b = " WHERE ";
            }
            $where['where'] = $b."LOWER(m.title) LIKE :search";
            if (User::$roles[$this->_role] == 'administrator' && !$this->filter_uid) {
                $where['where'] .= " OR LOWER(u.username) LIKE :search";
            }
        }
        if (!empty($this->sort)) {
            if ($this->sort == "created" || $this->sort == "updated") {
                $order = "m.".$this->sort . " {$this->dir}";
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
            $this->_db->bindParam(":uid", $this->_uid, 'integer');
        } else {
            if ($this->filter_uid) {
                $this->_db->bindParam(":uid_q", $this->filter_uid, 'integer');
            }
        }
        if (!empty($this->search)) {
            $this->_db->bindParam(":search", "%{$this->search}%", 'string');
        }
        $this->results = $this->_db->fetchAllObject();
        $this->row_count = $this->_db->rowCount();

        return $this;
    }

    /**
     * Implemented show method
     *
     * @param int $id The map identifier.
     *
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
        $this->_db->bindParam(":mid", $id, 'integer');

        $record = $this->_db->fetchFirstObject();
        $data['mid'] = ($record) ? $record->mid : "";
        $data['map'] = ($record) ? json_decode($record->map, true) : "";
        $data['status'] = ($data['map']) ? 'ok' : 'failed';

        return $data;
    }

    /**
     * Implemented create method
     *
     * @param array $params The parameters from the router
     *
     * @return array $output
     */
    public function create($params)
    {
        $data = array(
            'uid' => $this->_uid,
            'title' => $params['save']['title'],
            'map' => json_encode($params),
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
        $this->_db->bindParam(":uid", $this->_uid, 'integer');
        $this->_db->bindParam(":title", $data['title'], 'string');
        $record = $this->_db->fetchFirstObject($sql);

        $output = array();
        $output['status'] = "ok";

        if ($record) {
            unset($data['created']);
            $this->_db->queryUpdate('maps', $data, 'mid='.$record->mid);
            $output['mid'] = $record->mid;
        } else {
            $output['mid'] = $this->_db->queryInsert('maps', $data);
        }

        return $output;
    }

    /**
     * Implemented update method
     *
     * @param int $id The identifer
     *
     * @return void
     */
    public function update($id)
    {
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The map identifier.
     *
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
            $this->_db->bindParam(":mid", $id, 'integer');
        } else {
            $sql .= " AND uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bindParam(":mid", $id, 'integer');
            $this->_db->bindParam(":uid", $this->_uid, 'integer');
        }
        $this->_db->execute();
        return array("status" => "ok");
    }

}