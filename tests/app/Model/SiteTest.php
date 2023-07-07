<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\DB;

final class SiteTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $dbfile = "/opt/indieweb/tests/fixtures/testdb.sqlite3";
        unlink($dbfile);
        touch($dbfile);
        $this->db = new DB($dbfile);
        $sql = "CREATE TABLE Sites (url TEXT PRIMARY KEY, active INTEGER, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, profile text);
        CREATE TABLE SiteChecks (url TEXT KEY, datetime TEXT DEFAULT CURRENT_TIMESTAMP, result TEXT);
        INSERT INTO Sites VALUES (
            'https://vonexplaino.com/',
            1,
            '2001-01-01 00:00:00',
            'Dude');
        INSERT INTO Sites VALUES (
            'https://lapse.nerdvana.org.au/',
            1,
            '2002-01-01 00:00:00',
            'Dude');
        INSERT INTO Sites values (
            'https://no.com/',
            0,
            '2002-05-05 00:00:00',
            'Dude');
        INSERT INTO Sites VALUES (
            'https://grift.com/',
            1,
            '2003-01-01 00:00:00',
            'Dude');";
        $this->db->getInstance()->exec($sql);
    }
    public function testRandomGetsResult(): void
    {
        $site = new App\Model\Site(
            $this->db
        );
                $one = $site->randomActive();
        $this->assertArrayHasKey('url', $one);
    }
    public function testPreviousGetsResult(): void
    {

        $site = new App\Model\Site(
            $this->db
        );
        $one = $site->previousActive('https://lapse.nerdvana.org.au/index.html');
        $this->assertIsArray($one);
        $this->assertArrayHasKey('url', $one);
        $this->assertEquals($one['url'], 'https://vonexplaino.com/');
    }

    public function testNextGetsResult(): void
    {
        $site = new App\Model\Site(
            $this->db
        );
        $this->assertSame('App\Model\Site', get_class($site));
        $one = $site->nextActive('https://lapse.nerdvana.org.au/index.html');
        $this->assertIsArray($one);
        $this->assertArrayHasKey('url', $one);
        $this->assertEquals($one['url'], 'https://grift.com/');
        
        $one = $site->nextActive('https://vonexplaino.com/bob/builder');
        $this->assertIsArray($one);
        $this->assertArrayHasKey('url', $one);
        $this->assertEquals($one['url'], 'https://lapse.nerdvana.org.au/');
    }
}