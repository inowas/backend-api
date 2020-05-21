<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

function runCommand($command) {
    echo (sprintf("Executing: %s\r\n", $command));
    passthru($command);
}

if (isset($_ENV['BOOTSTRAP_CLEAR_DB_ENV'])) {
    // executes the commands:
    // php bin/console doctrine:database:drop --force
    runCommand(sprintf(
        'APP_ENV=%s php "%s/../bin/console" doctrine:database:drop --force',
        $_ENV['BOOTSTRAP_CLEAR_DB_ENV'],
        __DIR__
    ));

    // php bin/console doctrine:database:create
    runCommand(sprintf(
        'APP_ENV=%s php "%s/../bin/console" doctrine:database:create',
        $_ENV['BOOTSTRAP_CLEAR_DB_ENV'],
        __DIR__
    ));

    // php bin/console doctrine:schema:create
    runCommand(sprintf(
        'APP_ENV=%s php "%s/../bin/console" doctrine:schema:create',
        $_ENV['BOOTSTRAP_CLEAR_DB_ENV'],
        __DIR__
    ));
}

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
