<?php
namespace SimpleMappr;

/**
 * Rest.class.php released under MIT License
 * Basic utility functions to coordinate handling of RESTful actions
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse {{{
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
 * }}}
 */
class Rest
{
    protected $id;

    /**
     * Detect type of request and perform appropriate method
     */
    public function restful_action()
    {
        $verb = $_SERVER['REQUEST_METHOD'];

        switch($verb) {
        case 'GET':
            if ($this->id) {
                $this->show($this->id);
            } else {
                $this->index();
            }
            break;

        case 'PUT':
            $this->update();
            break;

        case 'POST':
            $this->create();
            break;

        case 'DELETE':
            $this->destroy($this->id);
            break;

        default:
        }
        return $this;
    }

    public function not_implemented()
    {
        Header::set_header('json');
        http_response_code(501);
        echo json_encode(array("status" => "fail", "message" => "Not implemented"));
    }
}

interface RestMethods
{
    public function index();
    public function show($id);
    public function create();
    public function update();
    public function destroy($id);
}