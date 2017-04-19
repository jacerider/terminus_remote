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
    // $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';

    // $cmd = 'sh -c "' . escapeshellarg('terminus site:create ' . $machine_name . ' "' . $label . '" "Drupal 8" --org="' . $organization . '"') . '" | at now';;

    // $cmd = 'sh sleep 10; terminus whoami > "' . $log . '" 2>&1 & echo $!';
    // $cmd = 'sh terminus site:create ' . $machine_name . ' "' . $label . '" "Drupal 8" --org="' . $organization . '" > "' . $log . '" 2>&1 & echo $!';

    // exec($cmd);
    $cmd = 'terminus aliases';
    // $cmd = 'terminus site:create ' . $machine_name . ' "' . $label . '" "Drupal 8" --org="' . $organization . '"';
    $log = $this->get('pantheon')['log_path'] . $machine_name . '.txt';
    $pid = $this->get('pantheon')['log_path'] . $machine_name . '.pid.txt';
    $command = 'sh -c ' . escapeshellarg(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $log, $pid));
    $this->logger->info($cmd);

    $process = new BackgroundProcess($cmd);
    $process->run($log);
    $process->stop();

    // exec($command);
    // die;
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
  $pid = $this->get('pantheon')['log_path'] . $machine_name . '.pid.txt';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    if (!empty($data)) {
      $return['error'] = terminusError(explode("\n", $data));
      $return['status'] = empty($return['error']);
      $return['data'] = $data;
      ksm($data);
      unlink($log);
      unlink($pid);
    }
  }
  return $response->withJson($return);
})->setName('create_status');

/**
 * @author Micheal Mouner
 * @param String $commandJob
 * @return Integer $pid
 */
function psExec($commandJob, $log = '/dev/null'){
  $command = 'sh ' . $commandJob . ' > ' . $log . ' 2>&1 & echo $!';
  exec($command, $op);
  $pid = (int) $op[0];
  if ($pid != "") {
    return $pid;
  }
  return false;
}


/**
 * Find output error.
 */
function terminusError($return) {
  $error = NULL;
  foreach ($return as $message) {
    $message = trim($message);
    if (substr($message, 0, 7) === '[error]') {
      $error = trim(substr($message, 7));
    }
  }
  return $error;
}
