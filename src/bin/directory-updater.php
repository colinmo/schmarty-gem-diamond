<?php

chdir(__DIR__ . '/..');
include('vendor/autoload.php');

// set up db
$db = new \App\DB(\Config::$dbPath);

// set up models
$sites = new \App\Model\Site($db);

$base = 'https://xn--sr8hvo.ws/'; // \Config::$base

if (count($argv) > 1) {
  $site = $sites->getSite($argv[1]);
  if ($site === false) {
    echo "No such site '${argv[1]}'\n";
    exit(1);
  }
  $sitesToCheck = [ $site ];
} else {
  $sitesToCheck = $sites->getActiveSitesWithProfiles();
}

echo "Checking " . count($sitesToCheck) . " sites...\n";

$delay = 0;
foreach ($sitesToCheck as $site) {
  sleep($delay);
  echo "${site['url']}";
  $card = new \App\Util\ProfileCheck($site['url'], \Config::$useragent);
  $sites->setProfile($site['url'], $card->getCard());
  echo " " . (!empty($card->getCard()) ? '✅' : '❌') . "\n";
  foreach($card->getErrors() as $err) {
    echo " - " . $err . "\n";
  }
  $delay = 0.5 + (mt_rand() / mt_getrandmax());
}
