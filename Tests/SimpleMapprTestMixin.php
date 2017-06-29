<?php

/**
 * SimpleMapprMixin trait functions used to simplify tests
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2013 David P. Shorthouse
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
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

trait SimpleMapprTestMixin
{

    /*
     * Set the request method
     *
     * @param string $type Default is "GET"
     *
     * @return void
     */
    public function setRequestMethod($type = 'GET')
    {
        $_SERVER['REQUEST_METHOD'] = $type;
    }

    /*
     * Clear the request method
     *
     * @return void
     */
    public function clearRequestMethod()
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST']);
    }

    /*
     * Set the request
     *
     * @param array $array The request array
     *
     * @return void
     */
    public function setRequest($array)
    {
        $_REQUEST = $array;
    }

    /*
     * Clear temporary files from the public/tmp directory
     *
     * @return void
     */
    public function clearTmpFiles()
    {
        $dirItr = new \RecursiveDirectoryIterator(dirname(__DIR__) . '/public/tmp');
        foreach (new \RecursiveIteratorIterator($dirItr, \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if ($file->isFile() && $file->getFilename()[0] !== ".") {
                @unlink($file->getPathname());
            }
        }
    }

    /*
     * Get an HTTP response from a POST request
     *
     * @param string $url The URL to send
     * @param array $params An associative array of parameters, default empty array
     * @param string $type The type of request to send, default = "GET"
     *
     * @return array ["body" => body, "code" => responseCode, "mime" => responseMIMEType]
     */
    public function httpRequest($url, $params = [], $type = "GET")
    {
        $data = '';
        foreach($params as $k => $v) {
            $data .= $k . '='.$v.'&'; 
        }
        rtrim($data, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($type == "GET") {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return ["body" => $body, "code" => $code, "mime" => $mime];
    }
}