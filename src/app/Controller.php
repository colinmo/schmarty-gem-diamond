<?php

namespace App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller
{

    private ResponseInterface $response;
    private \League\Plates\Engine $templates;
    private \App\Model\Site $site;
    private \App\Model\SiteCheck $siteCheck;

    public function __construct(ResponseInterface $response, \League\Plates\Engine $templates, \App\Model\Site $site, \App\Model\SiteCheck $siteCheck)
    {
        $this->response = $response;
        $this->templates = $templates;
        $this->site = $site;
        $this->siteCheck = $siteCheck;
        $this->templates->addData(['hostname' => $_SERVER['SERVER_NAME']]);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('index')->withStatus(200);
    }

    public function terms(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('terms')->withStatus(200);
    }

    public function dashboard(ServerRequestInterface $request): ResponseInterface
    {
        $me = $_SESSION['token']['me'];
        $site = $this->site->getSite($me, true);
        $site['profile'] = $this->profileFromCard($me, $site['profile']);
        $checks = $this->siteCheck->getSiteChecks($me);
        $this->templates->addData([
            'site' => $site,
            'checks' => $checks
        ]);
        return $this->render('dashboard')->withStatus(200);
    }

    public function checkLinks(ServerRequestInterface $request): ResponseInterface
    {
        $flash = $request->getAttribute('flash');
        $me = $_SESSION['token']['me'];
        $site = $this->site->getSite($me, true);
        if (!$site) {
            $flash->setError('site_not_found', 'Site "' . htmlspecialchars($me) . '" was not found.');
            return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $bases = array_merge([\Config::$base], \Config::$allowedLinkDomains);
        $check = new \App\Util\LinksCheck($me, $bases, \Config::$useragent);
        $this->siteCheck->addSiteCheck($me, $check->getErrors());
        $this->site->setActive($me, $check->isActive());

        return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function checkProfile(ServerRequestInterface $request): ResponseInterface
    {
        $flash = $request->getAttribute('flash');
        $me = $_SESSION['token']['me'];
        $site = $this->site->getSite($me, true);
        if (!$site) {
            $flash->setError('site_not_found', 'Site "' . htmlspecialchars($me) . '" was not found.');
            return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $card = new \App\Util\ProfileCheck($me, \Config::$useragent);
        $this->site->setProfile($me, $card->getCard());
        return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function removeProfile(ServerRequestInterface $request): ResponseInterface
    {
        $flash = $request->getAttribute('flash');
        $me = $_SESSION['token']['me'];
        $site = $this->site->getSite($me, true);
        if (!$site) {
            $flash->setError('site_not_found', 'Site "' . htmlspecialchars($me) . '" was not found.');
            return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $this->site->setProfile($me, false);
        return $this->response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function directory(ServerRequestInterface $request): ResponseInterface
    {
        $sites = $this->site->getActiveSitesWithProfiles();
        $profiles = array_map(
            function ($site) {
                return $this->profileFromCard($site['url'], $site['profile']);
            },
            $sites
        );
        return $this->render('directory', ['profiles' => $profiles])->withStatus(200);
    }

    public function random(ServerRequestInterface $request, array $args): ResponseInterface
    {
        // if $args['slug'] is present this is an old-style URL.
        // but we don't do anything with that knowledge so it's just a lonely comment.

        // if $request->getHeader('referer')[0] is present, that's where the visitor came from.
        // but we don't do anything with that knowledge so it's just a lonely comment.

        // get a random active site and redirect to it!
        $site = $this->site->randomActive();
        return $this->response->withHeader('Location', $site['url'])->withStatus(302);
    }

    public function next(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (isset($request->getHeader('referer')[0])) {
            $site = $this->site->nextActive($request->getHeader('referer')[0]);
            return $this->response->withHeader('Location', $site['url'])->withStatus(302);
        }
        return $this->random($request, $args);
    }

    public function previous(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if (isset($request->getHeader('referer')[0])) {
            $site = $this->site->previousActive($request->getHeader('referer')[0]);
            return $this->response->withHeader('Location', $site['url'])->withStatus(302);
        }
        return $this->random($request, $args);
    }

    protected function profileFromCard($url, $card)
    {

        if (empty($card)) {
            return false;
        }

        $card = json_decode($card, true);

        $u = parse_url($url);
        $cute_path = isset($u['path']) ? preg_replace('/\/(index.html)?$/', '', $u['path']) : '';
        $cute_url = preg_replace('/^www./', '', $u['host']) . $cute_path;
        $profile = [
            'cute_url' => htmlspecialchars($cute_url),
            'url' => htmlspecialchars($url),
        ];
        if (!array_key_exists('properties', $card)) {
            return $profile;
        }
        foreach (['name', 'note', 'photo'] as $prop) {
            if (array_key_exists($prop, $card['properties'])) {
                $val = $card['properties'][$prop][0];
                if (is_array($val)) {
                    // photo can be an array with url and alt. as u-photo it should have a
                    // `value` that's the URL we want.
                    if (array_key_exists('value', $val)) {
                        $val = $val['value'];
                    } else {
                        // some unexpected nesting here, treat it as missing.
                        $val = false;
                    }
                }
                if ($val) {
                    $profile[$prop] = htmlspecialchars($val);
                }
            }
        }
        return $profile;
    }

    protected function render($template, $data = [])
    {
        $this->response->getBody()->write($this->templates->render($template, $data));
        return $this->response;
    }
}
