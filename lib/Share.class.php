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
class Share extends Rest implements RestMethods
{
    private $_dir;
    private $_db;
    private $_uid;
    private $_role;

    function __construct($id)
    {
        session_start();
        Session::select_locale();
        $this->id = (int)$id;
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
        $this->_dir = (isset($_GET['dir']) && in_array(strtolower($_GET['dir']), array("asc", "desc"))) ? $_GET["dir"] : "desc";
        
        $order = "m.created {$this->_dir}";
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == "created") {
                $order = "s.".$_GET['sort'] . " {$this->_dir}";
            }
            if ($_GET['sort'] == "username") {
                $order = "u.".$_GET['sort'] . " {$this->_dir}";
            }
            if ($_GET['sort'] == "title") {
                $order = "m.".$_GET['sort'] . " {$this->_dir}";
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
        $sql = "
            SELECT
                m.mid, m.map
            FROM 
                maps m
            INNER JOIN
                shares s ON (m.mid = s.mid)
            WHERE
                s.sid = :sid";

        $this->_db->prepare($sql);
        $this->_db->bind_param(":sid", $id, 'integer');

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
        $this->check_session();
        $data = array(
            'mid' => $_POST["mid"],
            'created' => time(),
        );
        $this->_db->query_insert('shares', $data);
        Header::set_header('json');
        echo json_encode(array("status" => "ok"));
    }

    /**
     * Implemented update method
     */
    public function update()
    {
        $this->check_session();
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
        $this->check_session();
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
        Header::set_header('json');
        echo json_encode(array("status" => "ok"));
    }

    private function check_session()
    {
        if (!isset($_SESSION['simplemappr'])) {
            Utilities::access_denied();
        }
        $this->_uid = (int)$_SESSION['simplemappr']['uid'];
        $this->_role = (isset($_SESSION['simplemappr']['role'])) ? (int)$_SESSION['simplemappr']['role'] : 1;
    }

    private function produce_output($rows)
    {
        $output  = '';
        if (count($rows) > 0) {
            $output .= '<table class="grid-shares">' . "\n";
            $output .= '<thead>' . "\n";
            $output .= '<tr>' . "\n";
            $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "title" && isset($_GET['dir'])) ? " ".$this->_dir : "";
            $output .= '<th class="left-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="title" href="#">'._("Title").'</a></th>';
            $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "username" && isset($_GET['dir'])) ? " ".$this->_dir : "";
            $output .= '<th class="left-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="username" href="#">'._("Username").'</a></th>';
            $sort_dir = (isset($_GET['sort']) && $_GET['sort'] == "created" && isset($_GET['dir'])) ? " ".$this->_dir : "";
            if (!isset($_GET['sort']) && !isset($_GET['dir'])) {
                $sort_dir = " desc";
            }
            $output .= '<th class="center-align"><a class="sprites-after ui-icon-triangle-sort'.$sort_dir.'" data-sort="created" href="#">'._("Created").'</a></th>';
            $output .= '</tr>' . "\n";
            $output .= '</thead>' . "\n";
            $output .= '<tbody>' . "\n";
            $i=0;
            foreach ($rows as $row) {
                $class = ($i % 2) ? 'class="even"' : 'class="odd"';
                $output .= '<tr '.$class.'>';
                $output .= '<td class="title"><a class="map-load" data-id="'.$row->mid.'" href="#">' . Utilities::check_plain(stripslashes($row->title)) . '</a></td>';
                $output .= '<td>' . Utilities::check_plain(stripslashes($row->username)) . '</td>';
                $output .= '<td class="center-align">' . gmdate("M d, Y", $row->created) . '</td>';
                $output .= '</tr>' . "\n";
                $i++;
            }
            $output .= '</tbody>' . "\n";
            $output .= '</table>' . "\n";
        }
         else {
            $output .= '<div id="sharedmaps" class="panel ui-corner-all"><p>'._("Maps shared by other authenticated users will appear here for you to reuse as templates in your account. You can share your saved maps with all other users from your My Maps panel.").'</p></div>';
        }

        header("Content-Type: text/html");
        echo $output;
    }

}