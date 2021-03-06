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
  authenticate($this->get('pantheon')['machine_token']);

  $return['message'] = '<h2>Initializing...</h2><p><em>Authenticating and validating request.</em></p>';
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

  if (isset($data['machine_name']) && isset($data['label']) && isset($data['organization']) && isset($data['upstream_id'])) {
    $machine_name = filter_var($data['machine_name'], FILTER_SANITIZE_STRING);
    $label = filter_var($data['label'], FILTER_SANITIZE_STRING);
    $organization = filter_var($data['organization'], FILTER_SANITIZE_STRING);
    $upstream_id = filter_var($data['upstream_id'], FILTER_SANITIZE_STRING);

    // Authenticate
    authenticate($this->get('pantheon')['machine_token']);

    $cmd = __DIR__ . "/../commands/create.bash $machine_name '$label' '$upstream_id' '$organization'";
    $log = $this->get('pantheon')['log_path'] . $machine_name . '.create.log';
    $this->logger->info($cmd);

    $process = new BackgroundProcess($cmd);
    $process->run($log);

    $return['message'] = '<h2>Starting site creation...</h2><p><em>Please wait.</em></p>';
    $return['status'] = 1;
  }
  else {
    $return['error'] = 'Create process failed. Missing parameters.';
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
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.create.log';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    $messages = explode("\n", $data);
    if (!empty($data)) {
      $message = messageFind($messages, '[notice]') ?: 'Starting Install';
      $return['message'] = '<h2>' . $message . '</h2><p><em>So much is happening behind this white background... we could show you... <strong>but we would have to kill you.</strong></em></p>';
      $return['error'] = messageFind($messages);
      $return['data'] = $data;
      if ($return['error']) {
        // unlink($log);
      }
      else {
        $success = messageFind($messages, '[success]');
        if ($success) {
          $return['message'] = $success;
          $return['status'] = 1;
          // unlink($log);
        }
      }
    }
  }
  else {
    $return['error'] = 'Create process failed.';
  }
  return $response->withJson($return);
})->setName('create_status');

/**
 * Install site.
 */
$app->get('/install/{machine_name}', function (Request $request, Response $response, $args) {
  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];
  $return['message'] = 'Starting site installation...';

  // Authenticate
  authenticate($this->get('pantheon')['machine_token']);

  $cmd = __DIR__ . '/../commands/install.bash ' . $machine_name;
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.install.log';

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
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.install.log';
  if (file_exists($log)) {
    $data = file_get_contents($log);
    $messages = explode("\n", $data);
    $return['message'] = '<h2>Installing your site...</h2><p><em>So much is happening behind this white background... <strong>keep waiting</strong></em></p>';
    if (!empty($data)) {
      $message = messageFind($messages, '[notice]');
      $return['message'] = '<h2>' . $message . '</h2><p><em>So much is happening behind this white background... <strong>keep waiting</strong></em></p>';
      $return['error'] = messageFind($messages);
      $return['data'] = $data;
      $bglog = $this->get('pantheon')['log_path'] . $machine_name . '.install.bg.log';
      $return['log'] = file_get_contents($bglog);
      if ($return['error']) {
        // unlink($log);
      }
      else {
        $success = messageFind($messages, '[success]');
        if ($success) {
          $return['message'] = $success;
          $return['status'] = 1;
          // unlink($log);
        }
      }
    }
  }
  else {
    $return['error'] = 'Installation process failed.';
  }
  return $response->withJson($return);
})->setName('install_status');

/**
 * Url site route.
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
 * Login site route.
 */
$app->get('/login/{machine_name}', function (Request $request, Response $response, $args) {
  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];

  $cmd = 'terminus drush "' . $machine_name . '" -- user-login 1';
  $return['url'] = exec($cmd);
  $return['status'] = $return['url'] ? TRUE : FALSE;

  return $response->withJson($return);
})->setName('url');

/**
 * Delete site route.
 */
$app->get('/delete/{machine_name}', function (Request $request, Response $response, $args) {
  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];

  // Authenticate
  authenticate($this->get('pantheon')['machine_token']);

  $cmd = __DIR__ . '/../commands/delete.bash ' . $machine_name;

  $data = shell_exec($cmd);
  $messages = explode("\n", $data);
  $return['message'] = messageFind($messages, '[success]');
  $return['error'] = messageFind($messages);
  $return['status'] = $return['error'] ? FALSE : TRUE;

  return $response->withJson($return);
})->setName('url');

/**
 * TEST
 */
$app->get('/test/{machine_name}', function (Request $request, Response $response, $args) {

  // $result = unserialize(shell_exec('terminus ssh-key:list --format="php"'));
  // return $result;
  // return $response->withJson($result);
  // return $result;
  // return json_decode($result);

  $machine_name = filter_var($args['machine_name'], FILTER_SANITIZE_STRING);
  $return = [];

  // Authenticate
  authenticate($this->get('pantheon')['machine_token']);

  $cmd = __DIR__ . '/../commands/test.bash ' . $machine_name;
  $log = $this->get('pantheon')['log_path'] . $machine_name . '.test.log';
  fopen($log, 'w');
  return shell_exec($cmd);
  // $process = new BackgroundProcess($cmd);
  // $process->run($log);

  return $response->withJson($return);
})->setName('url');

/**
 * Authentication
 */
function authenticate($machine_token) {
  // Authenticate
  $cmd = 'terminus auth:login';
  if ($machine_token) {
    $cmd .= ' --machine-token=' . $machine_token;
  }
  exec($cmd);

  if ($machine_token) {
    $key = __DIR__ . '/../.ssh/id_rsa.pub';
    if (!file_exists($key)) {
      $existing = shell_exec('terminus ssh-key:list --format="php"');
      if ($existing) {
        $existing = unserialize($existing);
        if (is_array($existing)) {
          foreach ($existing as $data) {
            if ($data['comment'] == 'terminus-remote') {
              exec('terminus ssh-key:remove ' . $data['id']);
            }
          }
          exec('ssh-keygen -t rsa -N "" -C "terminus-remote" -f "/app/.ssh/id_rsa"');
          exec('terminus ssh-key:add "/app/.ssh/id_rsa.pub"');
        }
      }
    }
  }
}

/**
 * Find output error.
 */
function messageFind($return, $find = '[error]') {
  $results = [];
  foreach ($return as $message) {
    $message = trim($message);
    if (strtolower(substr($message, 0, strlen($find))) == strtolower($find)) {
      $found = trim(substr($message, strlen($find)));
      $results[] = !empty($found) ? $found : $message;
    }
  }
  return !empty($results) ? end($results) : NULL;
}
