<?php
// Front controller: bootstrap the application.

// Start session for login handling
session_start();

// Composer style autoloader
require_once __DIR__ . '/../app/Core/Autoloader.php';
\app\Core\Autoloader::register();

// Include helpers globally
require_once __DIR__ . '/../app/helpers.php';

// Run the application
$app = new \app\Core\App();
$app->run();