<?php

namespace App\Model;

class SiteCheck {

    private $db;

    public function __construct(\App\DB $db) {
        $this->db = $db->getInstance();
    }

    public function getSiteChecks(String $url, int $max = 3) {
        // fetch it from the DB
        $query = $this->db->prepare('SELECT * from SiteChecks where url = :url ORDER BY datetime DESC LIMIT :max');
        if ($query->execute([$url, $max])) {
            $result = $query->fetchAll();
            if (!empty($result)) {
                // munge each result for display
                return array_map(
                    function ($row) {
                        $errors = json_decode($row['result'], true);
                        $row['errors'] = $errors;

                        $oldLinkErrors = array_filter($errors, function ($err) {
                            return preg_match('/^Old emoji-style.*/', $err);
                        });

                        $row['active'] = empty($errors) || (!empty($oldLinkErrors));

                        return $row;
                    },
                    $result
                );
            }
        }
        return [];
    }

    public function addSiteCheck(String $url, array $errors) {
        $query = $this->db->prepare('INSERT INTO SiteChecks (url, result) VALUES (:url, :result)');
        if ($query->execute([$url, json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)])) {
            return true;
        }

        return false;
    }

}
