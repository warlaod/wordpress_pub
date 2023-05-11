<?php

if (getenv('ENV') == 'dev') {
  require '/var/www/html/vendor/autoload.php'; // Composer's autoloader
} else {
  require '/home/example/vendor/autoload.php';
}

$local_filedir  = getenv('ENV') == 'dev' ? '/var/www/logs/cron/crawler' : '/home/example/logs/cron/crawler';

for ($i=7; $i > -1; $i--) {
  $old_filename = $local_filedir . "cron_$i.log";
  $new_filename = $local_filedir . "cron_" . strval($i + 1) . ".log";
  copy($old_filename, $new_filename);
}

$local_filedir  = getenv('ENV') == 'dev' ? '/var/www/logs/cron/analyzer' : '/home/example/logs/cron/analyzer';

for ($i = 7; $i > -1; $i--) {
  $old_filename = $local_filedir . "cron_$i.log";
  $new_filename = $local_filedir . "cron_" . strval($i + 1) . ".log";
  copy($old_filename, $new_filename);
}
