<?php
chdir('..');
include('vendor/autoload.php');

use Schmarty\Micropubkit;

$app = new Micropubkit\IndieAuthEngine(dirname(__FILE__).'/../views', [
  'base'      => \Config::$base,
  'scope'     => \Config::$scope,
  'useragent' => \Config::$useragent,
]);

// set up db

$app->container->addShared(App\DB::class)
  ->addArgument(\Config::$dbPath);

// set up models
$app->container->addShared(App\Model\Site::class)->addArgument(App\DB::class);
$app->container->addShared(App\Model\SiteCheck::class)->addArgument(App\DB::class);

// set up controller for welcome and forms
$app->container->addShared(App\Controller::class)
  ->addArgument(Psr\Http\Message\ResponseInterface::class)
  ->addArgument(League\Plates\Engine::class)
  ->addArgument(App\Model\Site::class)
  ->addArgument(App\Model\SiteCheck::class);

// allow login everywhere but be chill about it
$app->route->middleware($app->optionalAuthMiddleware);

// set up app-specific routes
$app->route->get('/', 'App\\Controller::index')->setName('loginForm');
$app->route->get('/terms', 'App\\Controller::terms');
$app->route->get('/directory', 'App\\Controller::directory');

// ring navigation
$app->route->get('/next', 'App\\Controller::random');
$app->route->get('/previous', 'App\\Controller::random');
$app->route->get('/{slug}/next', 'App\\Controller::random');
$app->route->get('/{slug}/previous', 'App\\Controller::random');

$app->route->group('/dashboard', function (\League\Route\RouteGroup $route) {
  $route->get('/', 'App\\Controller::dashboard')->setName('loginSuccess');
  $route->post('/check-links', 'App\\Controller::checkLinks');
  $route->post('/check-profile', 'App\\Controller::checkProfile');
  $route->post('/remove-profile', 'App\\Controller::removeProfile');
})->middleware($app->requiresAuthMiddleware);

$app->handleRequest();
