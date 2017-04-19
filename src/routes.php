<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Routes

$app->get('/', function (Request $request, Response $response, $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");

  // Render index view
  return $this->renderer->render($response, 'index.html.twig', $args);
})->setName('home');

$app->get('/name/{name}', function (Request $request, Response $response, $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/name' route");

  // Render index view
  return $this->renderer->render($response, 'index.html.twig', $args);
})->setName('name');


$app->get('/test', function (Request $request, Response $response, $args) {
  $return = [];

  // Authenticate
  $machine_token = $this->get('pantheon')['machine_token'];
  $return['machine_token'] = $machine_token;

  $cmd = 'terminus auth:login';
  if ($machine_token) {
    $cmd .= ' --machine-token=' . $machine_token;
  }
  exec($cmd, $return['auth:login']['message'], $return['auth:login']['status']);

  $authenticated = exec('terminus auth:whoami');

  if ($authenticated) {
    exec('terminus site:list', $return['site:list']['message'], $return['site:list']['status']);
  }

  return $response->withJson($return);
})->setName('home');
