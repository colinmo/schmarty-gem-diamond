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
     * @return string 
     * @throws PDOException 
     */
    public function previousActive(string $referrer): string
    {
        $query = $this->db->prepare("SELECT previous from Sites WHERE :referrer like url||'%' AND active = 1 ORDER BY length(url) LIMIT 1");
        if ($query->execute([$referrer])) {
            $return = $query->fetch();
            if ($return) {
                return $return['previous'];
            }
        }
        return '/';
    }

    /**
     * Return the active site that's the next one to the specfied referrer
     * 
     * @param string $referrer URL the next link was clicked on
     * @return string 
     * @throws PDOException 
     */
    public function nextActive(string $referrer): string
    {
        $query = $this->db->prepare("SELECT next from Sites WHERE :referrer like url||'%' AND active = 1 ORDER BY length(url) LIMIT 1");
        if ($query->execute([$referrer])) {
            $return = $query->fetch();
            if ($return) {
                return $return['next'];
            }
        }
        return '/';
    }

    /**
     * Return ALL Sites in the Sites table
     * 
     * @return array
     * @throws PDOException 
     */
    public function all(): array
    {
        $query = $this->db->prepare('SELECT * FROM Sites');
        return ($query->execute() ? $query->fetchAll() : []);
    }

    public function getActiveSitesWithProfiles(): array|false
    {
        $query = $this->db->prepare('SELECT * FROM Sites WHERE active = 1 AND profile IS NOT NULL ORDER BY timestamp DESC');
        return ($query->execute() ? $query->fetchAll() : []);
    }

    // Updated to remove from ring if inactive, or add back to ring if active
    public function setActive(String $url, bool $active)
    {
        $existing = $this->getSite($url, false);
        if (!!$existing['active'] == !!$active) {
            // No change
            return true;
        }
        $this->db->beginTransaction();
        if ($active) {
            // Add back to ring
            $target = $this->randomActive();
            $query = $this->db->prepare('UPDATE Sites SET active = :active, previous = :prev, next = :next WHERE url = :url');
            $query->execute([true, $target['url'], $target['next'], $url]);
            $query = $this->db->prepare('UPDATE sites SET next = :url WHERE url = :orig');
            $query->execute([$url, $target['url']]);
            $query = $this->db->prepare('UPDATE sites SET previous = :url WHERE url = :orig');
            $query->execute([$url, $target['next']]);
        } else {
            // Remove from ring
            $query = $this->db->prepare('UPDATE Sites SET active = :active, previous = :prev, next = :next WHERE url = :url');
            $ok = $query->execute([false, '', '', $url]);
            $query = $this->db->prepare('UPDATE sites SET next = :url WHERE url = :orig');
            $query->execute([$existing['next'], $existing['previous']]);
            $query = $this->db->prepare('UPDATE sites SET previous = :url WHERE url = :orig');
            $query->execute([$existing['previous'], $existing['next']]);
        }
        
        return $this->db->commit();
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
