<?php

/**
 * SimpleMapprMixin trait functions used to simplify tests
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
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
        foreach ($params as $k => $v) {
            $data .= $k . '='.$v.'&';
        }
        rtrim($data, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_URL, $url);

        switch($type) {
            case "GET":
                curl_setopt($ch, CURLOPT_URL, $url . "?" . $data);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, count($data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return ["body" => $body, "code" => $code, "mime" => $mime];
    }

    /**
     * Check if two files are identical.
     *
     * @param string $fn1 First file directory.
     * @param string $fn2 Second file directory.
     * @return bool
     */
    public function filesIdentical($fn1, $fn2)
    {
        if (filetype($fn1) !== filetype($fn2)) {
            return false;
        }
        if (filesize($fn1) !== filesize($fn2)) {
            return false;
        }
        if (!$fp1 = fopen($fn1, 'rb')) {
            fclose($fp1);
            return false;
        }

        if (!$fp2 = fopen($fn2, 'rb')) {
            fclose($fp2);
            return false;
        }

        $same = true;
        while (!feof($fp1) and !feof($fp2)) {
            if (fread($fp1, 4096) !== fread($fp2, 4096)) {
                $same = false;
                break;
            }
        }

        if (feof($fp1) !== feof($fp2)) {
            $same = false;
        }

        fclose($fp1);
        fclose($fp2);

        return $same;
    }

    /**
     * Check if two images are very similar.
     *
     * @param string $fn1 First image directory.
     * @param string $fn2 Second image directory.
     * @return bool
     */
    public function imagesSimilar($fn1, $fn2)
    {
        $similar = false;

        $image1 = new \Imagick($fn1);
        $image2 = new \Imagick($fn2);
        $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);
        if ($result[1] < 0.01) {
            $similar = true;
        }
        return $similar;
    }

    /**
     * Produce clean output buffer from a Mappr object.
     *
     * @param object $mapp A Mappr object
     * @param bool $with_level Check the output buffer level
     * @return output buffer
     */
    public function ob_cleanOutput($mappr, $with_level = false)
    {
        ob_start();
        if ($with_level) {
            $level = ob_get_level();
        }
        echo $mappr->createOutput();
        $output = ob_get_clean();
        if ($with_level) {
            if (ob_get_level() > $level) {
                ob_end_clean();
            }
        }
        return $output;
    }
}
