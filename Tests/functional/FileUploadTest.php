<?php

/**
 * Unit tests for File upload handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class FileUploadTest extends SimpleMapprTest
{

    /**
     * Test upload an unrecognized file.
     */
    public function testUploadBadFile()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $file_input = $this->webDriver->findElement(WebDriverBy::id('fileInput'));
        $file_input->setFileDetector(new LocalFileDetector());
        $file_input->sendKeys(dirname(__DIR__) . "/files/sample3.docx");
        $this->webDriver->executeScript("$('#fileInput').trigger('change');", array());

        $message_box = $this->webDriver->findElement(WebDriverBy::id('badFile'));
        $this->assertTrue($message_box->isDisplayed());
        $this->assertEquals("Only files of type text are accepted.", $message_box->getText());
    }

    /**
     * Test upload an unrecognized file.
     */
    public function testUploadLargeFile()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $file_input = $this->webDriver->findElement(WebDriverBy::id('fileInput'));
        $file_input->setFileDetector(new LocalFileDetector());
        $file_input->sendKeys(dirname(__DIR__) . "/files/sample4.txt");
        $this->webDriver->executeScript("$('#fileInput').trigger('change');", array());

        $message_box = $this->webDriver->findElement(WebDriverBy::id('tooMuchData'));
        $this->assertTrue($message_box->isDisplayed());
        $this->assertEquals("A maximum of " . MAXNUMTEXTAREA . " data fields is supported.", $message_box->getText());
    }

    /**
     * Test upload a text file.
     */
    public function testUploadTextFile()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $file_input = $this->webDriver->findElement(WebDriverBy::id('fileInput'));
        $file_input->setFileDetector(new LocalFileDetector());
        $file_input->sendKeys(dirname(__DIR__) . "/files/sample.txt");
        $this->webDriver->executeScript("$('#fileInput').trigger('change');", array());
        parent::waitOnMap();

        $link->click();

        $title1 = $this->webDriver->findElement(WebDriverBy::name('coords[0][title]'));
        $data1 = $this->webDriver->findElement(WebDriverBy::name('coords[0][data]'));

        $title2 = $this->webDriver->findElement(WebDriverBy::name('coords[1][title]'));
        $data2 = $this->webDriver->findElement(WebDriverBy::name('coords[1][data]'));

        $title3 = $this->webDriver->findElement(WebDriverBy::name('coords[2][title]'));
        $data3 = $this->webDriver->findElement(WebDriverBy::name('coords[2][data]'));

        $this->assertEquals("Pardosa moesta", $title1->getAttribute("value"));
        $this->assertEquals("45,-120\n40,-110\n32,-100\n27.77,-150.6\n35.25,-120.7\n33.1,-100.5", $data1->getAttribute("value"));

        $this->assertEquals("Xysticus canadensis", $title2->getAttribute("value"));
        $this->assertEquals("52.6,-100\n48.9,-110\n51.0,-99\n46.666,-108\n\n58d31m58sN,100°21'44\"W", $data2->getAttribute("value"));

        $this->assertEquals("Trochosa terricola", $title3->getAttribute("value"));
        $this->assertEquals("47, -80\n56, -130", $data3->getAttribute("value"));
    }

    /**
     * Test upload a csv file.
     */
    public function testUploadCSVFile()
    {
        parent::setUpPage();
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Point Data'));
        $link->click();

        $file_input = $this->webDriver->findElement(WebDriverBy::id('fileInput'));
        $file_input->setFileDetector(new LocalFileDetector());
        $file_input->sendKeys(dirname(__DIR__) . "/files/sample2.txt");
        $this->webDriver->executeScript("$('#fileInput').trigger('change');", array());
        parent::waitOnMap();

        $link->click();

        $title1 = $this->webDriver->findElement(WebDriverBy::name('coords[0][title]'));
        $data1 = $this->webDriver->findElement(WebDriverBy::name('coords[0][data]'));

        $title2 = $this->webDriver->findElement(WebDriverBy::name('coords[1][title]'));
        $data2 = $this->webDriver->findElement(WebDriverBy::name('coords[1][data]'));

        $title3 = $this->webDriver->findElement(WebDriverBy::name('coords[2][title]'));
        $data3 = $this->webDriver->findElement(WebDriverBy::name('coords[2][data]'));

        $title4 = $this->webDriver->findElement(WebDriverBy::name('coords[3][title]'));
        $data4 = $this->webDriver->findElement(WebDriverBy::name('coords[3][data]'));

        $this->assertEquals("Pardosa moesta", $title1->getAttribute("value"));
        $this->assertEquals("45.755\t-110.12\n55.6\t-101\n48\t-109", $data1->getAttribute("value"));

        $this->assertEquals("Pardosa fuscula", $title2->getAttribute("value"));
        $this->assertEquals("47.9\t-112", $data2->getAttribute("value"));

        $this->assertEquals("Pardosa xerampelina", $title3->getAttribute("value"));
        $this->assertEquals("48.9\t-103.55\n43.02\t-105.9", $data3->getAttribute("value"));

        $this->assertEquals("Trochosa terricola", $title4->getAttribute("value"));
        $this->assertEquals("45.5\t-103.8\n46\t-100\n47.7\t-110.9", $data4->getAttribute("value"));
    }
}