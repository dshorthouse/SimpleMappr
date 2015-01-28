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
    public function index()
    {
        $this->dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
        $this->sort = (isset($_GET['sort'])) ? $_GET['sort'] : "";

        $order = "m.created {$this->dir}";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == "created") {
                $order = "s.".$_GET['sort'] . " {$this->dir}";
            }
            if ($_GET['sort'] == "username") {
                $order = "u.".$_GET['sort'] . " {$this->dir}";
            }
            if ($_GET['sort'] == "title") {
                $order = "m.".$_GET['sort'] . " {$this->dir}";
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
    public function create()
    {
        $data = array(
            'mid' => $_POST["mid"],
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