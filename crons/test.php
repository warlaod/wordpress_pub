<?php
require 'module/cron_init.php';

$index_url = 'http://www.stardrifter.org/cgi-bin/ref.cgi';

$htmlgetter = new HTMLGetter(parse_url($index_url, PHP_URL_SCHEME) . '://' . parse_url($index_url, PHP_URL_HOST));
$matcher = new Matcher();
$dbaccessor = new DBAccessor();


