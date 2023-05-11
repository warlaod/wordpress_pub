<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

function load_logger()
{
  $logger = new Logger('default');
  $log_path = __DIR__ . "/../../log/esacs.com.php.log";
  $logger->pushHandler(new RotatingFileHandler($log_path, 14));

  $logger->pushHandler(new StreamHandler($log_path, Logger::DEBUG));
  $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG)); // 標準出力へ書き込む
  return $logger;
}
