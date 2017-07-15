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
use SimpleMappr\Controller\User;

/**
 * Map model for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Map implements RestMethods
{
    /**
     * Total number of records
     *
     * @var int $total
     */
    public $total;

    /**
     * Filter results by username
     *
     * @var string $filter_username
     */
    public $filter_username;

    /**
     * Filter results by user identifier
     *
     * @var int $filter_uid
     */
    public $filter_uid;

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
     * Search string
     *
     * @var string $search
     */
    public $search;

    /**
     * Database query row count
     *
     * @var int $row_count
     */
    public $row_count;

    /**
     * Role for user defined in $roles
     *
     * @var int $_role
     */
    private $_user;

    /**
     * Database connection object
     *
     * @var object $_db
     */
    private $_db;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_user = (new User)->showByHash($_SESSION['simplemappr']['hash']);
        $this->_db = Database::getInstance();
    }

    /**
     * Implemented index method
     *
     * @param array $params The parameters from the router
     *
     * @return object $this
     */
    public function index($params)
    {
        $this->dir = (array_key_exists('dir', $params) && in_array(strtolower($params['dir']), ["asc", "desc"])) ? $params['dir'] : "desc";
        $this->sort = (array_key_exists('sort', $params)) ? $params['sort'] : "";
        $this->search = (array_key_exists('search', $params)) ? $params['search'] : "";
        $this->filter_uid = (array_key_exists('uid', $params)) ? (int)$params['uid'] : null;
        $this->filter_username = "";

        $username = "u.username, ";
        $where['user'] = " WHERE m.uid = :uid";
        $limit = "";

        if (User::isAdministrator($this->_user)) {
            if ($this->filter_uid) {
                $where['user'] = " WHERE m.uid = :uid_q";
            } else {
                $username = "";
                $where['user'] = "";
                $limit = " LIMIT 100";
            }
        }

        $sql = "SELECT
                    {$username} COUNT(m.mid) AS total
                FROM
                     maps m
                INNER JOIN
                     users u ON (m.uid = u.uid)
                {$where['user']}";

        $this->_db->prepare($sql);

        if (!User::isAdministrator($this->_user)) {
            $this->_db->bindParam(":uid", $this->_user->results->uid);
        }

        if ($this->filter_uid) {
            $this->_db->bindParam(":uid_q", $this->filter_uid);
            $this->filter_username = $this->_db->fetchFirstObject()->username;
        }
        $this->total = $this->_db->fetchFirstObject()->total;

        $order = "m.created {$this->dir}";

        $b = "";
        if (!empty($this->search)) {
            if (User::isAdministrator($this->_user) && !$this->filter_uid) {
                $b = " WHERE ";
                unset($where['user']);
            }
            $where['where'] = $b."LOWER(m.title) LIKE :search";
            if (User::isAdministrator($this->_user) && !$this->filter_uid) {
                $where['where'] .= " OR LOWER(u.username) LIKE :search";
            }
        }
        if (!empty($this->sort)) {
            if ($this->sort == "title" || $this->sort == "created" || $this->sort == "updated") {
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
        if (!User::isAdministrator($this->_user)) {
            $this->_db->bindParam(":uid", $this->_user->results->uid, 'integer');
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
     * @return array $data
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
     * @param array $content The parameters from the router
     *
     * @return array $output
     */
    public function create($content)
    {
        $data = [
            'uid' => $this->_user->results->uid,
            'title' => $content['save']['title'],
            'map' => json_encode($content),
            'created' => time(),
            'updated' => time()
        ];

        //see if user's map by same title already exists
        $sql = "SELECT
                    mid
                FROM
                    maps
                WHERE
                    uid = :uid AND title = :title";
        $this->_db->prepare($sql);
        $this->_db->bindParam(":uid", $this->_user->results->uid, 'integer');
        $this->_db->bindParam(":title", $data['title'], 'string');
        $record = $this->_db->fetchFirstObject($sql);

        $output = [];
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
     * @param array  $content Any parameters
     * @param string $where   The where clause
     *
     * @return void
     */
    public function update($content, $where)
    {
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The map identifier.
     *
     * @return array status
     */
    public function destroy($id)
    {
        $sql = "DELETE 
                FROM
                    maps
                WHERE 
                    mid = :mid";
        if (User::isAdministrator($this->_user)) {
            $this->_db->prepare($sql);
            $this->_db->bindParam(":mid", $id, 'integer');
        } else {
            $sql .= " AND uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bindParam(":mid", $id, 'integer');
            $this->_db->bindParam(":uid", $this->_user->results->uid, 'integer');
        }
        $this->_db->execute();
        return ["status" => "ok"];
    }
}
