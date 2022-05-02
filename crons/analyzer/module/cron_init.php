<?php

/**
 * A pseudo-cron daemon for scheduling WordPress tasks.
 *
 * WP-Cron is triggered when the site receives a visit. In the scenario
 * where a site may not receive enough visits to execute scheduled tasks
 * in a timely manner, this file can be called directly or via a server
 * cron daemon for X number of times.
 *
 * Defining DISABLE_WP_CRON as true and calling this file directly are
 * mutually exclusive and the latter does not rely on the former to work.
 *
 * The HTTP request to this file will not slow down the visitor who happens to
 * visit when a scheduled cron event runs.
 *
 * @package WordPress
 */
require_once 'dbaccessor.php';
require_once 'matcher.php';
require_once 'filemanager.php';
require_once 's3manager.php';
require_once 'logger.php';
require_once 'functions.php';

ignore_user_abort(true);

/* Don't make the request block till we finish, if possible. */
if (function_exists('fastcgi_finish_request') && version_compare(phpversion(), '7.0.16', '>=')) {
  if (!headers_sent()) {
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
  }

  fastcgi_finish_request();
}

if (!empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON')) {
  die();
}

/**
 * Tell WordPress we are doing the cron task.
 *
 * @var bool
 */
define('DOING_CRON', true);

if (!defined('ABSPATH')) {
  /** Set up WordPress environment */
  require_once __DIR__ . '/../../../wp-load.php';
}

require_once(ABSPATH . 'wp-admin/includes/image.php');
