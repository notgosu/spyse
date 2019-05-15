<?php
require './vendor/autoload.php';

if ($argc !== 2) {
    echo "Usage: php index.php [path/to/file].\n";
    exit(1);
}
$path = $argv[1];

$parser = new ParseCommand($path);
$parser->run();
