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

    private $_role;
    private $_dir;
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
        $this->_dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
        $order = "u.access {$this->_dir}";

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
                $order = $order.$sort." ".$this->_dir;
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
        $this->produce_output($this->_db->fetch_all_object());
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

    /**
     * Produce the HTML output for user list
     *
     * @param array $rows Rows returned from the resultset
     * @return void
     */
    private function produce_output($rows)
    {
        $output  = '';
        $output .= '<table class="grid-users">' . "\n";
        $output .= '<thead>' . "\n";
        $output .= '<tr>' . "\n";
        $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "username" && isset($_GET['dir'])) ? " ".$this->_dir : "";
        $output .= '<th class="left-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="username" href="#">'._("Username").'</a></th>';
        $output .= '<th class="left-align">'._("Email").'</th>';
        $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "num" && isset($_GET['dir'])) ? " ".$this->_dir : "";
        $output .= '<th><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="num" href="#">'._("Maps").'</a></th>';
        $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "access" && isset($_GET['dir'])) ? " ".$this->_dir : "";
        if (!isset($_GET['sort']) && !isset($_GET['dir'])) {
            $sort_dir = " desc";
        }
        $output .= '<th><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="access" href="#">'._("Last Access").'</a></th>';
        $output .= '<th class="actions">'._("Actions").'<a href="#" class="sprites-after toolsRefresh"></a></th>';
        $output .= '</tr>' . "\n";
        $output .= '</thead>' . "\n";
        $output .= '<tbody>' . "\n";
        $i=0;
        foreach ($rows as $row) {
            $class = ($i % 2) ? 'class="even"' : 'class="odd"';
            $output .= '<tr '.$class.'>';
            $output .= '<td><a class="user-load" data-uid="'.$row->uid.'" href="#">';
            $output .= Utilities::check_plain(stripslashes($row->username));
            $output .= '</a></td>';
            $output .= '<td>'.Utilities::check_plain(stripslashes($row->email)).'</td>';
            $output .= '<td class="usermaps-number">'.$row->num.'</td>';
            $access = ($row->access) ? gmdate("M d, Y", $row->access) : '-';
            $output .= '<td class="usermaps-center">'.$access.'</td>';
            $output .= '<td class="actions">';
            if (!$row->role || self::$roles[$row->role] !== 'administrator') {
                $output .= '<a class="sprites-before user-delete" data-id="'.$row->uid.'" href="#">'._("Delete").'</a>';
            }
            $output .= '</td>';
            $output .= '</tr>' . "\n";
            $i++;
        }
        $output .= '</tbody>' . "\n";
        $output .= '</table>' . "\n";

        header("Content-Type: text/html");
        echo $output;
    }

}