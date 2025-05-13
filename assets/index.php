<?php

// Check for Composer autoloader
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die('Please run "composer install" in the project root directory');
}

// Bootstrap autoloader
require_once $autoloadFile;

use App\Controllers\GitController;

header('Content-Type: application/json');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Route handling for views
switch ($path) {
    case '/':
        require __DIR__ . '/../app/views/home.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/../app/views/404.php';
        break;
}

// API routes for Git operations
$controller = new GitController();
$method = $_SERVER['REQUEST_METHOD'];
$route = $_SERVER['REQUEST_URI'];

switch ($route) {
    case '/api/git/clone':
        if ($method === 'POST') {
            try {
                echo $controller->cloneRepo();
            } catch (Exception $e) {
                echo json_encode(['error' => 'Git Clone failed: ' . $e->getMessage()]);
            }
        }
        break;

    case '/api/git/pull':
        if ($method === 'POST') {
            try {
                echo $controller->pull();
            } catch (Exception $e) {
                echo json_encode(['error' => 'Git Pull failed: ' . $e->getMessage()]);
            }
        }
        break;

    case '/api/git/push':
        if ($method === 'POST') {
            try {
                echo $controller->push();
            } catch (Exception $e) {
                echo json_encode(['error' => 'Git Push failed: ' . $e->getMessage()]);
            }
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
}

