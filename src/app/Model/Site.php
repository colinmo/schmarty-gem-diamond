<?php
namespace App\Model;

use PDOException;

/**
 * For interacting with Sites. CRUD, Navigation between, de/activate
 */
final class Site
{

    /** @var \PDO $db PDO Database object */
    private $db;

    public function __construct(\App\DB $db)
    {
        $this->db = $db->getInstance();
    }

    /**
     * Retrieve a site from database by URL
     **/
    public function getSite(string $url, bool $create = false): array|false
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
        $this->addSite($url);
        $query = $this->db->prepare('SELECT * from Sites where url = :url');
        if ($query->execute([$url])) {
            $result = $query->fetchAll();
            if (!empty($result)) {
                return $result[0];
            }
        }
        return false;
    }

    /**
     * Retrieve an active site from database by random order
     */
    public function randomActive(): array
    {
        // fetch it from the DB
        $query = $this->db->prepare('SELECT * from Sites WHERE active = 1 ORDER BY RANDOM() LIMIT 1');
        return ($query->execute() ? $query->fetch() : ['url' => '/']);
    }

    /**
     * Return the active site that's the previous one to the specfied referrer
     * 
     * @param string $referrer URL the previous link was clicked on
     * @return array 
     * @throws PDOException 
     */
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

    /**
     * Return the active site that's the next one to the specfied referrer
     * 
     * @param string $referrer URL the next link was clicked on
     * @return array 
     * @throws PDOException 
     */
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

    /**
     * Return ALL Sites in the Sites table
     * 
     * @return array|false 
     * @throws PDOException 
     */
    public function all(): array|false
    {
        $query = $this->db->prepare('SELECT * FROM Sites');
        return ($query->execute() ? $query->fetchAll() : []);
    }

    public function getActiveSitesWithProfiles(): array|false
    {
        $query = $this->db->prepare('SELECT * FROM Sites WHERE active = 1 AND profile IS NOT NULL ORDER BY timestamp DESC');
        return ($query->execute() ? $query->fetchAll() : []);
    }

    public function setActive(String $url, bool $active)
    {
        $query = $this->db->prepare('UPDATE Sites SET active = :active WHERE url = :url');
        return $query->execute([$active, $url]);
    }

    public function setProfile(String $url, $card)
    {
        $query = $this->db->prepare('UPDATE Sites SET profile = :profile WHERE url = :url');
        $profile = empty($card) ? null : json_encode($card, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $query->execute([$profile, $url]);
    }

    public function unchecked()
    {
        $query = $this->db->prepare('SELECT s.* FROM Sites s LEFT OUTER JOIN SiteChecks sc ON sc.url = s.url WHERE sc.datetime IS NULL');
        return ($query->execute() ? $query->fetchAll() : []);
    }

    /**
     * Insert a site into the ring
     * 
     * @param string $url URL to add
     * @return bool 
     * @throws PDOException 
     */
    protected function addSite(string $url)
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
