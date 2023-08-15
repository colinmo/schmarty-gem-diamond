<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\DB;

final class SiteCheckTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $dbfile = __DIR__ . "/../../fixtures/testdb.sqlite3";
        unlink($dbfile);
        touch($dbfile);
        $this->db = new DB($dbfile);
        $sql = "CREATE TABLE Sites (
            url TEXT PRIMARY KEY,
            active INTEGER,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            profile text,
            next TEXT,
            previous TEXT);
        CREATE TABLE SiteChecks (
            url TEXT KEY,
            datetime TEXT DEFAULT CURRENT_TIMESTAMP,
            result TEXT
        );
        INSERT INTO Sites VALUES (
            'https://vonexplaino.com/',
            1,
            '2001-01-01 00:00:00',
            'Dude',
            'https://grift.com/',
            'https://lapse.nerdvana.org.au');
        INSERT INTO Sites VALUES (
            'https://lapse.nerdvana.org.au/',
            1,
            '2002-01-01 00:00:00',
            'Dude',
            'https://vonexplaino.com/',
            'https://grift.com/');
        INSERT INTO Sites values (
            'https://no.com/',
            0,
            '2002-05-05 00:00:00',
            'Dude',
            '',
            '');
        INSERT INTO Sites VALUES (
            'https://grift.com/',
            1,
            '2003-01-01 00:00:00',
            'Dude',
            'https://lapse.nerdvana.org.au/',
            'https://vonexplaino.com/');
        INSERT INTO SiteChecks VALUES (
        'https://vonexplaino.com/',
        '2001-01-01 00:00:00',
        '[]');
        INSERT INTO SiteChecks VALUES (
        'https://lapse.nerdvana.org.au/',
        null,
        null);
        INSERT INTO SiteChecks VALUES (
        'https://grift.com/',
        null,
        '[\"bob\",\"Old emoji-style\"]');
        INSERT INTO SiteChecks VALUES (
        'https://no.com/',
        null,
        '[\"Old emoji-style\"]');";
        $this->db->getInstance()->exec($sql);
    }
    protected function tearDown(): void
    {
        unlink(__DIR__ . "/../tests/fixtures/testdb.sqlite3");
    }

    public function testSiteChecksExists(): void
    {
        $site_check = new App\Model\SiteCheck(
            $this->db
        );
        $x = $site_check->getSiteChecks("https://vonexplaino.com/");
        $this->assertLessThan(4, count($x));
    }

    public function testSiteCheckNoErrors(): void
    {
        $site_check = new App\Model\SiteCheck(
            $this->db
        );
        $x = $site_check->getSiteChecks("https://vonexplaino.com/");
        $this->assertTrue($x[0]['active']);
    }
}