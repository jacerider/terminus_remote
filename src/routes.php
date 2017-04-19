<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Cocur\BackgroundProcess\BackgroundProcess;

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

/**
 * Auth route.
 */
$app->get('/authenticate', function (Request $request, Response $response, $args) {
  $return = [];

  // Authenticate
  $machine_token = $this->get('pantheon')['machine_token'];

  $cmd = 'terminus auth:login';
  if ($machine_token) {
    $cmd .= ' --machine-token=' . $machine_token;
  }
  exec($cmd);

  $return['whoami'] = exec('terminus auth:whoami');
  $return['status'] = $return['whoami'] ? TRUE : FALSE;
  return $response->withJson($return);
})->setName('authenticate');

/**
 * Create site route.
 */
$app->post('/create', function (Request $request, Response $response) {
  $return = [];
  $return['status'] = 0;

  $data = $request->getParsedBody();

  if (isset($data['machine_name']) && isset($data['label']) && isset($data['organization'])) {
    $machine_name = filter_var($data['machine_name'], FILTER_SANITIZE_STRING);
    $label = filter_var($data['label'], FILTER_SANITIZE_STRING);
    $organization = filter_var($data['organization'], FILTER_SANITIZE_STRING);

    $cmd = 'terminus site:create ' . $machine_name . ' "' . $label . '" "Drupal 8" --org="' . $organization . '"';
    $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';
    $this->logger->info($cmd);

    $process = new BackgroundProcess($cmd);
    $process->run($log);

    $return['status'] = 1;
  }

  return $response->withJson($return);
})->setName('create');

/**
 * Create site route.
 */
$app->get('/create/status/{machine_name}', function (Request $request, Response $response, $args) {
  $return = [];
  $return['status'] = 0;

  $machine_name = $args['machine_name'];
  $return['machine_name'] = $machine_name;
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    $messages = explode("\n", $data);
    if (!empty($data)) {
      $return['message'] = 'Deploying site...';
      $return['error'] = messageFind($messages);
      $return['data'] = $data;
      if ($return['error']) {
        unlink($log);
      }
      else {
        $success = messageFind($messages, '[notice] Deployed CMS');
        if ($success) {
          $return['message'] = $success;
          $return['status'] = 1;
          unlink($log);
        }
      }
    }
  }
  return $response->withJson($return);
})->setName('create_status');

/**
 * Find output error.
 */
function messageFind($return, $find = '[error]') {
  $error = NULL;
  foreach ($return as $message) {
    $message = trim($message);
    if (substr($message, 0, strlen($find)) === $find) {
      $error = trim(substr($message, strlen($find)));
    }
  }
  return $error;
}
