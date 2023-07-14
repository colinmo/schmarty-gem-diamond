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
        'Cool, cool');
        INSERT INTO SiteChecks VALUES (
        'https://lapse.nerdvana.org.au/',
        null,
        null);";
        $this->db->getInstance()->exec($sql);
    }
    protected function tearDown(): void
    {
        unlink("/opt/indieweb/tests/fixtures/testdb.sqlite3");
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
        $this->assertEquals($one['url'], 'https://grift.com/');
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
        $this->assertEquals($one['url'], 'https://vonexplaino.com/');

        $one = $site->nextActive('https://vonexplaino.com/bob/builder');
        $this->assertIsArray($one);
        $this->assertArrayHasKey('url', $one);
        $this->assertEquals($one['url'], 'https://grift.com/');
    }

    public function testAddSiteSlotsIn(): void
    {
        $site = new App\Model\Site(
            $this->db
        );
        $bob = $site->getActiveSitesWithProfiles();
        $this->assertSame(count($bob), 3);
        $site->getSite('https://why.here.com', true);
        $bob = $site->getActiveSitesWithProfiles();
        $this->assertSame(count($bob), 3);
        $site->setProfile('https://why.here.com', 'Profile stuff');
        $bob = $site->getActiveSitesWithProfiles();
        $this->assertSame(count($bob), 4);
        $bob = $site->all();
        $this->assertSame(count($bob), 5);
    }

    public function testNonexistantSite(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->getSite('https://why.here.com', false);
        $this->assertSame(false, $result);
    }

    public function testExistantSite(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->getSite('https://vonexplaino.com/', false);
        $this->assertSame($result['url'], 'https://vonexplaino.com/');
    }

    public function testNonexistantPrevious(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->previousActive('https://why.here.com');
        $this->assertSame($result, ['url' => '/']);
    }

    public function testNonexistantNext(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->nextActive('https://why.here.com');
        $this->assertSame($result, ['url' => '/']);
    }

    public function testUnchecked(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->unchecked();
        $this->assertSame(3, count($result));
    }

    public function testActiveSetOk(): void
    {
        $site = new App\Model\Site($this->db);
        $result = $site->setActive('https://no.com/', true);
        $this->assertTrue($result);
        $asite = $site->getSite('https://no.com/');
        $this->assertSame($asite['active'], 1);
        $result = $site->setActive('https://no.com/', false);
        $this->assertTrue($result);
        $asite = $site->getSite('https://no.com/');
        $this->assertSame($asite['active'], '');
    }
    
    public function testAllNoDb()
    {   
        $site = new App\Model\Site($this->db);
        $query = $this->db->getInstance()->prepare('DELETE FROM Sites');
        $query->execute();
        $result = $site->all();
        $this->assertSame([], $result);

        
    }
}
