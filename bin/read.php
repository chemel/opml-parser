<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Alc\Opml\Reader;

if(!isset($argv[1])) {
    echo "Usage: php read.php <opml-file>\n";
    exit(1);
}

$reader = new Reader();
$opml = $reader->read(realpath($argv[1]));

print_r($opml);