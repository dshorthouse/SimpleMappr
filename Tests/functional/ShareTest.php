<?php

/**
 * Unit tests for citation handling
 * REQUIREMENTS: web server running as specified in phpunit.xml + Selenium
 *
 * PHP Version >= 5.6
 *
 * @author    David P. Shorthouse <davidpshorthouse@gmail.com>
 * @copyright 2010-2017 David P. Shorthouse
 * @license   MIT, https://github.com/dshorthouse/SimpleMappr/blob/master/LICENSE
 * @link      http://github.com/dshorthouse/SimpleMappr
 *
 */
class ShareTest extends SimpleMapprTestCase
{
    /**
     * Test content of share list
     */
    public function testDefaultSharesList()
    {
        parent::setSession();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'));
        $link->click();
        $this->assertContains("Sample Map Administrator", $this->shareContent());
    }

    /**
     * Test share count
     */
    public function testShareCount()
    {
        parent::setSession();

        $link = $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'));
        $link->click();
        $text = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='sharedmaps']/table/thead/tr[1]/th[1]"))->getText();
        $this->assertEquals("Title 1", $text);
    }

    /**
     * Test creation of share
     */
    public function testCreateShare()
    {
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Share", $link->getText());
        $link->click();
        parent::waitOnAjax();
        $this->assertEquals("Unshare", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertContains("Sample Map User", $this->shareContent());
    }

    /**
     * Test removal of a share
     */
    public function testRemoveShare()
    {
        parent::setSession();

        $this->webDriver->findElement(WebDriverBy::linkText('My Maps'))->click();
        $link = $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"));
        $this->assertEquals("Unshare", $link->getText());
        $link->click();
        parent::waitOnAjax();

        $this->assertEquals("Share", $this->webDriver->findElement(WebDriverBy::xpath("//div[@id='usermaps']/table/tbody/tr[1]/td[4]/a[1]"))->getText());
        $this->assertNotContains("Sample User Map", $this->shareContent());
    }

    /**
     * Get the content of the default share message when none exists
     */
    private function shareContent()
    {
        $this->webDriver->findElement(WebDriverBy::linkText('Shared Maps'))->click();
        return $this->webDriver->findElement(WebDriverBy::id('sharedmaps'))->getText();
    }
}
