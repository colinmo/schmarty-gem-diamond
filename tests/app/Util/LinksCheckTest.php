<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\DB;
use App\Util\LinksCheck;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

final class LinksCheckTest extends TestCase
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
        unlink("/opt/indieweb/tests/fixtures/testdb.sqlite3");
    }

    public function testGoodLink(): void
    {
        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><p><a href="http://localhost:89/previous">Previous</a>||<a href="http://localhost:89/next">Next</a></p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), []);
        $this->assertTrue($o->isActive());
    }

    public function testOldGoodLink(): void
    {
        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><p><a href="http://localhost:89/do/previous">Previous</a>||<a href="http://localhost:89/do/next">Next</a></p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), [
            'Old emoji-style link: /do/previous',
            'Old emoji-style link: /do/next',
            'Missing one or both links to http://localhost:89/next or http://localhost:89/previous'
        ]);
    }

    public function testWeirdLink(): void
    {
        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><p><a href="http://localhost:89/do/previous">Previous</a>||<a href="http://localhost:89/Steve">Steve</a></p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), [
            'Old emoji-style link: /do/previous',
            'Missing one or both links to http://localhost:89/next or http://localhost:89/previous',
            'Found unknown link: http://localhost:89/Steve'
        ]);
    }

    public function testGoodLinkMultiple(): void
    {
        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><p><a href="/">Dude</a><a href="http://localhost:89/">Sweet</a><a href="http://localhost:89/previous">Previous</a>||<a href="http://localhost:89/next">Next</a></p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), []);
        $this->assertTrue($o->isActive());
    }

    public function testConnectException(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new ConnectException('Connection refused', new Request('GET', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
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

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
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

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["URL returned status code 201"]);
    }

    public function testRequest401(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new Response(401, ['Content-Length' => 20], '<html><head></head><body><p>Permission denied</p></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["Error fetching URL. Client error: `GET 1` resulted in a `401 Unauthorized` response:\n<html><head></head><body><p>Permission denied</p></body></html>\n"]);
    }

    public function testRequestNoLinks(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], 'Steve? STEVE!'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["No links found on page. Parse error? Line 0, Col 0: Unexpected text. Ignoring: Steve? STEVE!"]);
    }

    public function testRequestWeirdLinks(): void
    {

        global $handlerStack;
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 20], '<html><head></head><body><a name="steve"></a>, <a href="">Mike</a></body></html>'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        $o = new LinksCheckMock(
            '1',
            array_merge([\Config::$base], \Config::$allowedLinkDomains),
            'bob/dosomething'
        );
        $this->assertEquals($o->getErrors(), ["Missing one or both links to http://localhost:89/next or http://localhost:89/previous"]);
    }

    public function testHttpClient(): void
    {
        $this->assertEquals(get_class(LinksCheck::makeHTTPClient("bob")), "GuzzleHttp\Client");
    }
}

class LinksCheckMock extends LinksCheck
{
    public static function makeHTTPClient(string $userAgent): HttpClient
    {
        global $handlerStack;
        return new HttpClient(['handler' => $handlerStack]);
    }
}
