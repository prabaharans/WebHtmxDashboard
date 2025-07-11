<?php
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// Include configuration and dependencies
require_once 'config/database.php';
require_once 'config/routes.php';
require_once 'models/Database.php';
require_once 'models/Task.php';
require_once 'models/Project.php';
require_once 'controllers/TaskController.php';
require_once 'controllers/ProjectController.php';
require_once 'controllers/DashboardController.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Simple router
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = dirname($_SERVER['SCRIPT_NAME']);
if ($base_path !== '/') {
    $uri = str_replace($base_path, '', $uri);
}

// Ensure URI starts with /
if (empty($uri) || $uri[0] !== '/') {
    $uri = '/' . $uri;
}

// Handle routes
$route_found = false;

foreach ($routes as $route) {
    $pattern = $route['pattern'];
    $controller = $route['controller'];
    $method = $route['method'];
    $http_method = isset($route['http_method']) ? $route['http_method'] : 'GET';
    
    // Check HTTP method
    if ($request_method !== $http_method) {
        continue;
    }
    
    // Convert route pattern to regex
    $regex_pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
    $regex_pattern = '#^' . $regex_pattern . '$#';
    
    if (preg_match($regex_pattern, $uri, $matches)) {
        $route_found = true;
        
        // Extract parameters
        $params = array_slice($matches, 1);
        
        // Create controller instance
        $controller_instance = new $controller($db);
        
        // Call controller method
        if (method_exists($controller_instance, $method)) {
            call_user_func_array([$controller_instance, $method], $params);
        } else {
            http_response_code(404);
            echo "Method not found";
        }
        break;
    }
}

if (!$route_found) {
    http_response_code(404);
    echo "Page not found";
}
?>
