<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\DB;
use App\Util\ProfileCheck;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

final class ProfileCheckTest extends TestCase
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
        unlink(__DIR__ . "/../../fixtures/testdb.sqlite3");
    }
    public function testConnectException(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new ConnectException('Connection refused', new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new ProfileCheckMock(
            'http://bob.com',
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["Error fetching URL. Connection refused"]);
    }

    public function testRequestException(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new RequestException('I refuse to talk', new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new ProfileCheckMock(
            'http://bob.com',
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["Error fetching URL. I refuse to talk"]);
    }
    public function testRequest201(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new Response(201, ['Content-Length' => 20], '<html><head></head><body><p>Permission denied</p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new ProfileCheckMock(
            '1',
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["URL returned status code 201"]);
    }

    public function testUninitialisedCard():void
    {
        global $handlerStack;
        $mock = new MockHandler([
            new RequestException('I refuse to talk', new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new ProfileCheckMock(
            'http://bob.com',
            'bob/dosomething'
        );
        $this->assertNotTrue($o->getCard());
    }
    public function testOKProfileCard(): void
    {
        
        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><a class="h-card" href="https://tantek.com/" rel="me">Tantek Çelik</a>, 
            <span class="h-card">
            <a class="p-name p-org u-url" href="https://microformats.org/">microformats.org</a>
            </span></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new ProfileCheckMock(
            'https://tantek.com/',
            'bob/dosomething'
        );
        $bob = $o->getCard();
        $this->assertEquals($bob['type'], ["h-card"]);
        $this->assertEquals($bob['properties']['name'], ["Tantek Çelik"]);
    }
    public function testHttpClient(): void
    {
        $this->assertEquals(get_class(ProfileCheck::makeHTTPClient("bob")), "GuzzleHttp\Client");
    }
}


class ProfileCheckMock extends ProfileCheck
{
    public static function makeHTTPClient(string $userAgent): HttpClient
    {
        global $handlerStack;
        return new HttpClient(['handler' => $handlerStack]);
    }
}
