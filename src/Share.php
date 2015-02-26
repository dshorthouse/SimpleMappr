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
 * Share handler for SimpleMappr
 *
 * @package SimpleMappr
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 */
class Share implements RestMethods
{
    public $sort;
    public $dir;
    public $results;

    private $_db;
    private $_uid;
    private $_role;

    function __construct()
    {
        $this->_db = new Database();
        $this->_uid = (int)$_SESSION['simplemappr']['uid'];
        $this->_role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
    }

    /**
     * Implemented index method
     */
    public function index($params)
    {
        $this->dir = (property_exists($params, 'dir') && in_array(strtolower($params->dir), array("asc", "desc"))) ? $params->dir : "desc";
        $this->sort = (property_exists($params, 'sort')) ? $params->sort : "";

        $order = "m.created {$this->dir}";
        if (!empty($this->sort)) {
            if ($this->sort == "created") {
                $order = "s.".$this->sort . " {$this->dir}";
            }
            if ($this->sort == "username") {
                $order = "u.".$this->sort . " {$this->dir}";
            }
            if ($this->sort == "title") {
                $order = "m.".$this->sort . " {$this->dir}";
            }
        }

        $sql = "
            SELECT
                s.mid, m.title, u.username, s.created
            FROM
                maps m
            INNER JOIN
                users u ON (m.uid = u.uid)
            INNER JOIN
                shares s ON (s.mid = m.mid)
            ORDER BY ".$order;

        $this->_db->prepare($sql);
        $this->results = $this->_db->fetch_all_object();
        return $this;
    }

    /**
     * Implemented show method
     *
     * @param int $id The Share identifier
     * @return void
     */
    public function show($id)
    {
    }

    /**
     * Implemented create method
     */
    public function create($params)
    {
        $mid = (property_exists($params, 'mid')) ? $params->mid : null;

        if (empty($mid)) {
            return array("status" => "error");
            exit();
        }

        $data = array(
            'mid' => $mid,
            'created' => time(),
        );
        $this->_db->query_insert('shares', $data);
        return array("status" => "ok");
    }

    /**
     * Implemented update method
     */
    public function update($id)
    {
    }

    /**
     * Implemented destroy method
     *
     * @param int $id The User identifier
     * @return void
     */
    public function destroy($id)
    {
        if (User::$roles[$this->_role] == 'administrator') {
            $sql = "DELETE 
                    FROM
                        shares
                    WHERE 
                        sid = :sid";
            $this->_db->prepare($sql);
            $this->_db->bind_param(":sid", $id, 'integer');
        } else {
            $sql = "DELETE s.*
                    FROM
                        shares s
                    INNER JOIN
                        maps m ON (m.mid = s.mid)
                    WHERE 
                        s.sid = :sid AND m.uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bind_param(":sid", $id, 'integer');
            $this->_db->bind_param(":uid", $this->_uid, 'integer');
        }
        $this->_db->execute();
        return array("status" => "ok");
    }

}