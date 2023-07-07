<?php

chdir(__DIR__ . '/..');
include('vendor/autoload.php');

// set up db
$db = new \App\DB(\Config::$dbPath);

// set up models
$sites = new \App\Model\Site($db);
$checks = new \App\Model\SiteCheck($db);

$bases = array_merge([\Config::$base], \Config::$allowedLinkDomains);

if (count($argv) > 1) {
  $site = $sites->getSite($argv[1]);
  if ($site === false) {
    echo "No such site '${argv[1]}'\n";
    exit(1);
  }
  $sitesToCheck = [ $site ];
} else {
  $sitesToCheck = $sites->unchecked();
  //$sitesToCheck = $sites->all();
}

echo "Checking " . count($sitesToCheck) . " sites...\n";

$delay = 0;
foreach ($sitesToCheck as $site) {
  sleep($delay);
  echo "${site['url']} ";
  $check = new \App\Util\LinksCheck($site['url'], $bases, \Config::$useragent);
  $checks->addSiteCheck($site['url'], $check->getErrors());
  $sites->setActive($site['url'], $check->isActive());
  echo ($check->isActive() ? '✅' : '❌') . "\n";
  foreach($check->getErrors() as $err) {
    echo " - " . $err . "\n";
  }

  $delay = 0.5 + (mt_rand() / mt_getrandmax());
}
