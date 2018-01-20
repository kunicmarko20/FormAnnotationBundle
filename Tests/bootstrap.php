<?php

// if the bundle is within a symfony project, try to reuse the project's autoload

$files = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../vendor/autoload.php',
    __DIR__.'/../../../../../app/autoload.php',
];

$autoload = false;
foreach ($files as $file) {
    if (is_file($file)) {
        include_once $file;
        $autoload = true;

        break;
    }
}

if (!$autoload) {
    die('Unable to find autoload.php file, please use composer to load dependencies:

wget http://getcomposer.org/composer.phar
php composer.phar install

Visit http://getcomposer.org/ for more information.

');
}
