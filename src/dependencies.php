<?php

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

// DIC configuration
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
  $settings = $c->get('settings');
  $view = new Twig($settings['renderer']['template_path'], [
    'debug' => $settings['displayErrorDetails'],
    'cache' => $settings['renderer']['template_path'],
  ]);

  // Instantiate and add Slim specific extension
  $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
  $view->addExtension(new TwigExtension($c['router'], $basePath));

  return $view;
};

// monolog
$container['logger'] = function ($c) {
  $settings = $c->get('settings')['logger'];
  $logger = new Logger($settings['name']);
  $logger->pushProcessor(new UidProcessor());
  $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
  return $logger;
};
