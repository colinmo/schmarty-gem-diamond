<?php

namespace App\Util;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Masterminds\HTML5;

class LinksCheck
{

    protected $errors = [];
    protected $active = false;

    public function __construct(string $url, array $allowedLinkDomains, string $userAgent)
    {
        $httpClient = $this->makeHTTPClient($userAgent);
        try {
            $response = $httpClient->request('GET', $url);
        } catch (ConnectException $e) {
            $this->errors[] = "Error fetching URL. {$e->getMessage()}";
            return;
        } catch (RequestException $e) {
            $this->errors[] = "Error fetching URL. {$e->getMessage()}";
            return;
        }

        if ($response->getStatusCode() !== 200) {
            $this->errors[] = "URL returned status code {$response->getStatusCode()}";
            return;
        }

        $html = new HTML5();
        $dom = $html->loadHTML($response->getBody());
        $links = $dom->getElementsByTagName('a');
        if ($links->length === 0) {
            $msg = "No links found on page. Parse error? ";
            if ($html->getErrors()) {
                $msg .= implode("\n", $html->getErrors());
            }
            $this->errors[] = $msg;
            return;
        }

        $linksByPath = [];
        foreach ($links as $link) {

            $hrefAttr = $link->attributes->getNamedItem('href');
            if (!$hrefAttr) {
                continue;
            }

            $href = $hrefAttr->nodeValue;
            if (!$href) {
                continue;
            }
            $matchedDomains = array_filter(
                $allowedLinkDomains,
                function ($siteBase) use ($href) {
                    return (substr($href, 0, strlen($siteBase)) === $siteBase);
                }
            );
            if (count($matchedDomains) === 0) {
                continue;
            }

            $path = parse_url($href, PHP_URL_PATH);
            $linksByPath[$path] = $href;
        }

        $expectedLinks = $this->expectedLinks();
        $fallbackLinks = $this->fallbackLinks();

        foreach ($linksByPath as $path => $href) {
            // we can ignore links to the root even tho they are cool
            if ($path === '/') {
                unset($linksByPath[$path]);
            }
            // see if `/next` and `/previous` are here.
            foreach ($expectedLinks as $name => $regex) {
                if (preg_match($regex, $path) === 1) {
                    unset($expectedLinks[$name]);
                    unset($linksByPath[$path]);
                }
            }
            // see if `/{slug}/next` and `/{slug}/previous` are here.
            foreach ($fallbackLinks as $name => $regex) {
                if (preg_match($regex, $path) === 1) {
                    unset($fallbackLinks[$name]);
                    unset($linksByPath[$path]);
                    $this->errors[] = "Old emoji-style link: {$path}";
                }
            }
        }

        if (empty($expectedLinks)) {
            $this->active = true;
        } else {
            $siteBase = $allowedLinkDomains[0];
            $this->errors[] = "Missing one or both links to {$siteBase}next or {$siteBase}previous";
        }

        if (empty($fallbackLinks)) {
            $this->active = true;
        }

        foreach ($linksByPath as $extraLink) {
            $this->errors[] = "Found unknown link: $extraLink";
        }
    }

    public function isActive()
    {
        return $this->active;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function expectedLinks()
    {
        return [
            'next' => '/^\/next$/',
            'previous' => '/^\/previous$/',
        ];
    }

    protected function fallbackLinks()
    {
        return [
            'next' => "/^\/[^\/]+\/next$/",
            'previous' => "/^\/[^\/]+\/previous$/",
        ];
    }

    protected function makeHTTPClient(string $userAgent)
    {
        return new HttpClient([
            'headers' => ['User-Agent' => $userAgent],
            'http_errors' => false,
            'timeout' => 15
        ]);
    }
}
