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
 * Create site status.
 */
$app->get('/create/{machine_name}/status', function (Request $request, Response $response, $args) {
  $return = [];
  $return['status'] = 0;

  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    $messages = explode("\n", $data);
    if (!empty($data)) {
      $message = messageFind($messages, '[notice]');
      $return['message'] = '<h2>' . $message . '</h2><p><em>So much is happening behind this white background...</em></p>';
      $return['error'] = messageFind($messages);
      $return['data'] = $data;
      if ($return['error']) {
        unlink($log);
      }
      else {
        $success = messageFind($messages, '[notice] Deployed');
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
 * Install site.
 */
$app->get('/install/{machine_name}', function (Request $request, Response $response, $args) {
  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];

  $cmd = 'terminus drush -n "' . $machine_name . '.dev"  -- site-install -y';
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';

  $process = new BackgroundProcess($cmd);
  $process->run($log);

  $return['status'] = 1;

  return $response->withJson($return);
})->setName('install');

/**
 * Install site status.
 */
$app->get('/install/{machine_name}/status', function (Request $request, Response $response, $args) {
  $return = [];
  $return['status'] = 0;

  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    $messages = explode("\n", $data);
    $return['message'] = '<h2>Installing your site...</h2><p><em>So much is happening behind this white background...</em></p>';
    if (!empty($data)) {
      $return['error'] = messageFind($messages);
      $return['data'] = $data;
      if ($return['error']) {
        unlink($log);
      }
      else {
        $success = messageFind($messages, 'Installation complete');
        if ($success) {
          $return['message'] = $success;
          $return['status'] = 1;
          unlink($log);
        }
      }
    }
  }
  return $response->withJson($return);
})->setName('install_status');

/**
 * Create site route.
 */
$app->get('/url/{machine_name}', function (Request $request, Response $response, $args) {
  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];

  $cmd = 'terminus env:view --print ' . $machine_name . '.dev';
  $return['url'] = exec($cmd);
  $return['status'] = $return['url'] ? TRUE : FALSE;

  return $response->withJson($return);
})->setName('url');

/**
 * Find output error.
 */
function messageFind($return, $find = '[error]') {
  $results = [];
  foreach ($return as $message) {
    $message = trim($message);
    if (strtolower(substr($message, 0, strlen($find))) === strtolower($find)) {
      $results[] = trim(substr($message, strlen($find)));
    }
  }
  return !empty($results) ? end($results) : NULL;
}
