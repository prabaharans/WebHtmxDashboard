<?php
// Routes configuration
$routes = [
    // Dashboard
    ['pattern' => '/', 'controller' => 'DashboardController', 'method' => 'index'],
    ['pattern' => '/dashboard', 'controller' => 'DashboardController', 'method' => 'index'],
    
    // Tasks
    ['pattern' => '/tasks', 'controller' => 'TaskController', 'method' => 'index'],
    ['pattern' => '/tasks/create', 'controller' => 'TaskController', 'method' => 'create'],
    ['pattern' => '/tasks/store', 'controller' => 'TaskController', 'method' => 'store', 'http_method' => 'POST'],
    ['pattern' => '/tasks/{id}', 'controller' => 'TaskController', 'method' => 'show'],
    ['pattern' => '/tasks/{id}/edit', 'controller' => 'TaskController', 'method' => 'edit'],
    ['pattern' => '/tasks/{id}/update', 'controller' => 'TaskController', 'method' => 'update', 'http_method' => 'POST'],
    ['pattern' => '/tasks/{id}/delete', 'controller' => 'TaskController', 'method' => 'delete', 'http_method' => 'POST'],
    ['pattern' => '/tasks/{id}/status', 'controller' => 'TaskController', 'method' => 'updateStatus', 'http_method' => 'POST'],
    ['pattern' => '/tasks/search', 'controller' => 'TaskController', 'method' => 'search'],
    ['pattern' => '/tasks/filter', 'controller' => 'TaskController', 'method' => 'filter'],
    
    // Projects
    ['pattern' => '/projects', 'controller' => 'ProjectController', 'method' => 'index'],
    ['pattern' => '/projects/create', 'controller' => 'ProjectController', 'method' => 'create'],
    ['pattern' => '/projects/store', 'controller' => 'ProjectController', 'method' => 'store', 'http_method' => 'POST'],
    ['pattern' => '/projects/{id}', 'controller' => 'ProjectController', 'method' => 'show'],
    ['pattern' => '/projects/{id}/edit', 'controller' => 'ProjectController', 'method' => 'edit'],
    ['pattern' => '/projects/{id}/update', 'controller' => 'ProjectController', 'method' => 'update', 'http_method' => 'POST'],
    ['pattern' => '/projects/{id}/delete', 'controller' => 'ProjectController', 'method' => 'delete', 'http_method' => 'POST'],
    
    // API endpoints for HTMX
    ['pattern' => '/api/tasks/kanban', 'controller' => 'TaskController', 'method' => 'kanban'],
    ['pattern' => '/api/dashboard/stats', 'controller' => 'DashboardController', 'method' => 'stats'],
];
?>
