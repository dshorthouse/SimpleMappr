<?php

/**
 * Unit tests for Logger class
 *
 * PHP Version 5.5
 *
 * @author  David P. Shorthouse <davidpshorthouse@gmail.com>
 * @link    http://github.com/dshorthouse/SimpleMappr
 * @license Copyright (C) 2013 David P. Shorthouse
 *
 */
class LoggerTest extends PHPUnit_Framework_TestCase
{
    protected $file;
    protected $logger;

    /**
     * Parent setUp function executed before each test
     */
    protected function setUp()
    {
        $this->file = ROOT."/log/logger.log";
        $this->clearFile();
        $this->logger = new \SimpleMappr\Logger($this->file);
    }

    /**
     * Parent tearDown function executed after each test
     */
    protected function tearDown()
    {
        $this->clearFile();
    }

    private function clearFile()
    {
        file_put_contents($this->file, "");
    }

    private function setSession()
    {
        $username = 'administrator';
        $locale = 'en_US';
        $user = array(
            "identifier" => $username,
            "username" => $username,
            "email" => "nowhere@example.com",
            "locale" => $locale
        );
        if ($username == 'administrator') {
            $role = array("role" => "2", "uid" => "1", "displayname" => "John Smith");
        } else {
            $role = array("role" => "1", "uid" => "2", "displayname" => "Jack Johnson");
        }
        $user = array_merge($user, $role);
        session_cache_limiter('nocache');
        session_start();
        session_regenerate_id();
        $_SESSION["simplemappr"] = $user;
        session_write_close();
    }

    private function readFile()
    {
        return file_get_contents($this->file);
    }

    public function test_write()
    {
        $this->logger->write("This is some content");
        $this->assertStringStartsWith("This is some content", $this->readFile());
    }

    public function test_read()
    {
        $this->setSession();
        $this->logger->write("This is some content");
        $this->assertStringStartsWith("This is some content", implode("", $this->logger->tail()));
    }

}