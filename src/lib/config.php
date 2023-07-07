<?php
class Config
{
    // Client's base URL
    public static $base = 'http://localhost:89/';

    // Extra allowed prefixes for webring links. ($base is always included)
    public static $allowedLinkDomains = [
        //'https://xn--sr8hvo.ws/',
    ];

    // User-agent string for requests this client makes
    public static $useragent = 'IndieWeb Web Ring/2.0';

    // A secret key, >= 64 characters long.
    public static $cookieSecret = 'replace with your very secret key';

    // Micropub scopes we want
    public static $scope = 'profile';

    // Path to database
    public static $dbPath = '/opt/indieweb/data/db.sqlite3';
}
