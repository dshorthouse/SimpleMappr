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
namespace SimpleMappr\Controller;

use SimpleMappr\Database;

/**
 * Share model for SimpleMappr
 *
 * @category  Class
 * @package   SimpleMappr
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 */
class Share implements RestMethods
{
    /**
     * @var string $sort Database column upon which to sort
     */
    public $sort;

    /**
     * @var string $dir Direction to sort: asc or desc
     */
    public $dir;

    /**
     * @var object $results Database query results
     */
    public $results;

    /**
     * @var int $_role Role for user defined in $roles
     */
    private $_user;

    /**
     * @var object $_db Database connection object
     */
    private $_db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_user = (new User)->show_by_hash($_SESSION['simplemappr']['hash']);
        $this->_db = Database::getInstance();
    }

    /**
     * Implemented index method
     *
     * @param array $params Parameters object from router
     *
     * @return object $this The class instance
     */
    public function index($params)
    {
        $this->dir = (array_key_exists('dir', $params) && in_array(strtolower($params['dir']), ["asc", "desc"])) ? $params['dir'] : "desc";
        $this->sort = (array_key_exists('sort', $params)) ? $params['sort'] : "";

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
        $this->results = $this->_db->fetchAllObject();
        return $this;
    }

    /**
     * Implemented show method
     *
     * @param int $id The Share identifier
     *
     * @return void
     */
    public function show($id)
    {
    }

    /**
     * Implemented create method
     *
     * @param array $content The parameters from the router.
     *
     * @return array status
     */
    public function create($content)
    {
        $mid = (array_key_exists('mid', $content)) ? $content["mid"] : null;

        if (empty($mid)) {
            return ["status" => "error"];
        } else {
            $data = [
                'mid' => $mid,
                'created' => time(),
            ];
            $this->_db->queryInsert('shares', $data);
            return ["status" => "ok"];
        }
    }

    /**
     * Implemented update method
     *
     * @param array $content An array of content
     * @param string $where The where string
     *
     * @return void
     */
    public function update($content, $where)
    {
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
        if (User::isAdministrator($this->_user)) {
            $sql = "DELETE 
                    FROM
                        shares
                    WHERE 
                        sid = :sid";
            $this->_db->prepare($sql);
            $this->_db->bindParam(":sid", $id, 'integer');
        } else {
            $sql = "DELETE s.*
                    FROM
                        shares s
                    INNER JOIN
                        maps m ON (m.mid = s.mid)
                    WHERE 
                        s.sid = :sid AND m.uid = :uid";
            $this->_db->prepare($sql);
            $this->_db->bindParam(":sid", $id, 'integer');
            $this->_db->bindParam(":uid", $this->_user->results->uid, 'integer');
        }
        $this->_db->execute();
        return ["status" => "ok"];
    }
}
