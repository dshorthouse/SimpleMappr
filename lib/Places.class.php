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

class Places extends Rest implements RestMethods
{
    protected $id;
    protected $db;

    function __construct($id = null)
    {
        $this->id = $id;
        Session::select_locale();
        $this->db = new Database();
        $this->restful_action();
    }

    /**
     * Implemented index method.
     */
    public function index()
    {
        if (isset($_REQUEST['filter'])) {
            $this->db->prepare("SELECT * FROM stateprovinces WHERE country LIKE :filter");
            $this->db->bind_param(':filter', '%'.$_REQUEST['filter'].'%', 'string');
            Header::set_header("html");
            $this->produce_output($this->db->fetch_all_object());
        } else if (isset($_REQUEST['term']) || $this->id) {
            $term = (isset($_REQUEST['term'])) ? $_REQUEST['term'] : $this->id;
            $this->db->prepare(
                "SELECT DISTINCT
                    sp.country as label, sp.country as value
                FROM
                    stateprovinces sp
                WHERE
                    sp.country LIKE :term
                ORDER BY
                    sp.country
                LIMIT 5"
            );
            $this->db->bind_param(':term', $term.'%', 'string');
            Header::set_header("json");
            echo json_encode($this->db->fetch_all_object());
        } else {
            $this->db->prepare("SELECT * FROM stateprovinces ORDER BY country, stateprovince");
            Header::set_header("html");
            $this->produce_output($this->db->fetch_all_object());
        }
    }

    /**
     * Implemented show method.
     *
     * @param int $id Identifier for places.
     * @return void
     */
    public function show($id)
    {
        $this->id = $id;
        $this->index();
    }

    /**
     * Implemented create method.
     */
    public function create()
    {
        $this->index();
    }

    /**
     * Implemented update method.
     */
    public function update()
    {
        $this->not_implemented();
    }

    /**
     * Implemented destroy method.
     *
     * @param int $id Identifier for the place.
     * @return void
     */
    public function destroy($id)
    {
        $this->not_implemented();
    }

    /**
     * Produce HTML table of places from an array of rows.
     *
     * @param array $rows An array of row data.
     */
    private function produce_output($rows)
    {
        $output  = '';
        $output .= '<table class="countrycodes">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<td class="title">'._("Country");
        $output .= '<input class="filter-countries" type="text" size="25" maxlength="35" value="" name="filter" />';
        $output .= '</td>';
        $output .= '<td class="code">ISO</td>';
        $output .= '<td class="title">'._("State/Province").'</td>';
        $output .= '<td class="code">'._("Code").'</td>';
        $output .= '<td class="example">'._("Example").'</td>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        $i = 0;
        foreach ($rows as $row) {
            $class = ($i % 2) ? 'class="even"' : 'class="odd"';
            $output .= '<tr '.$class.'>';
            $output .= '<td>' . $row->country . '</td>';
            $output .= '<td>' . $row->country_iso . '</td>';
            $output .= '<td>' . $row->stateprovince . '</td>';
            $output .= '<td>' . $row->stateprovince_code . '</td>';
            $example = ($row->stateprovince_code) ? $row->country_iso . '[' . $row->stateprovince_code . ']' : '';
            $output .= '<td>' . $example . '</td>';
            $output .= '</tr>';
            $i++;
        }
        $output .= '</tbody>';
        $output .= '</table>';
        echo $output;
    }

}