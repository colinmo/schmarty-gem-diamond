<?php

namespace App\Model;

class Site
{

    private $db;

    public function __construct(\App\DB $db)
    {
        $this->db = $db->getInstance();
    }

    public function getSite(String $url, bool $create = false)
    {
        // fetch it from the DB
        $query = $this->db->prepare('SELECT * from Sites where url = :url');
        if ($query->execute([$url])) {
            $result = $query->fetchAll();
            if (!empty($result)) {
                return $result[0];
            }
        }

        // wasn't in the DB. if we're not supposed to create it, return failure here.
        if ($create !== true) {
            return false;
        }

        // create it!
        return $this->addSite($url);
    }

    public function randomActive(): array
    {
        // fetch it from the DB
        $query = $this->db->prepare('SELECT * from Sites WHERE active = 1 ORDER BY RANDOM() LIMIT 1');
        if ($query->execute()) {
            return $query->fetch();
        }
        return ['url' => '/'];
    }

    public function previousActive(string $referrer): array
    {
        $query = $this->db->prepare("SELECT previous as url from Sites WHERE :referrer like url||'%' AND active = 1 ORDER BY length(url) LIMIT 1");
        if ($query->execute([$referrer])) {
            $return = $query->fetch();
            if ($return) {
                return $return;
            }
        }
        return ['url' => '/'];
    }

    public function nextActive(string $referrer): mixed
    {
        $query = $this->db->prepare("SELECT next as url from Sites WHERE :referrer like url||'%' AND active = 1 ORDER BY length(url) LIMIT 1");
        if ($query->execute([$referrer])) {
            $return = $query->fetch();
            if ($return) {
                return $return;
            }
        }
        return ['url' => '/'];
    }

    public function all()
    {
        $query = $this->db->prepare('SELECT * FROM Sites');
        if ($query->execute()) {
            return $query->fetchAll();
        }
        return [];
    }

    public function getActiveSitesWithProfiles(): array
    {
        $query = $this->db->prepare('SELECT * FROM Sites WHERE active = 1 AND profile IS NOT NULL ORDER BY timestamp DESC');
        if ($query->execute()) {
            return $query->fetchAll();
        }
        return [];
    }

    public function setActive(String $url, bool $active)
    {
        $query = $this->db->prepare('UPDATE Sites SET active = :active WHERE url = :url');
        if ($query->execute([$active, $url])) {
            return true;
        }
        return false;
    }

    public function setProfile(String $url, $card)
    {
        $query = $this->db->prepare('UPDATE Sites SET profile = :profile WHERE url = :url');
        $profile = empty($card) ? null : json_encode($card, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($query->execute([$profile, $url])) {
            return true;
        }
        return false;
    }

    public function unchecked()
    {
        $query = $this->db->prepare('SELECT s.* FROM Sites s LEFT OUTER JOIN SiteChecks sc ON sc.url = s.url WHERE sc.datetime IS NULL');
        if ($query->execute()) {
            return $query->fetchAll();
        }
        return [];
    }

    protected function addSite(String $url)
    {
        // Pick a random spot in the ring and put it in it
        $this->db->beginTransaction();
        $target = $this->randomActive();
        $query = $this->db->prepare('INSERT INTO Sites (url, active, next, previous) VALUES (:url, 1, :next, :previous)');
        $query->execute([$url, $target['next'], $target['url']]);
        $query = $this->db->prepare('UPDATE sites SET next = :url WHERE url = :orig');
        $query->execute([$url, $target['url']]);
        $query = $this->db->prepare('UPDATE sites SET previous = :url WHERE url = :orig');
        $query->execute([$url, $target['next']]);
        return $this->db->commit();
    }
}
