<?php

/**
 * SimpleMapprMixin trait functions used to simplify tests
 *
 * PHP Version 5.5
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
trait SimpleMapprMixin
{

    public function setRequest($type = 'GET')
    {
        $_SERVER['REQUEST_METHOD'] = $type;
    }

    public function clearRequest()
    {
        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST']);
    }

    public function clearTmpFiles()
    {
        $tmpfiles = glob(ROOT."/public/tmp/*.{jpg,png,tiff,pptx,docx,kml}", GLOB_BRACE);
        foreach ($tmpfiles as $file) {
            unlink($file);
        }
    }

    public function setMapprDefaults($class)
    {
        $class->set_shape_path(ROOT."/mapserver/maps")
            ->set_font_file(ROOT."/mapserver/fonts/fonts.list")
            ->set_tmp_path(ROOT."/public/tmp/")
            ->set_tmp_url(MAPPR_MAPS_URL)
            ->set_default_projection("epsg:4326")
            ->setMaxExtent("-180,-90,180,90");
        return $class;
    }

    public function httpPost($url, $params)
    {
        $postData = '';
        foreach($params as $k => $v) {
            $postData .= $k . '='.$v.'&'; 
        }
        rtrim($postData, '&');

        $ch = curl_init();  
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    

        $output = curl_exec($ch);

        curl_close($ch);
        return $output;
    }
}