const express = require('express');
const path = require('path');
const fs = require('fs');
const sqlite3 = require('sqlite3').verbose();

const app = express();
const PORT = 5000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static('assets'));

// Database setup
const dbPath = path.join(__dirname, 'database', 'task_manager.db');
const dbDir = path.dirname(dbPath);

// Create database directory if it doesn't exist
if (!fs.existsSync(dbDir)) {
    fs.mkdirSync(dbDir, { recursive: true });
}

// Initialize database
const db = new sqlite3.Database(dbPath);

// Create tables and insert sample data
db.serialize(() => {
    // Create projects table
    db.run(`
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    `);

    // Create tasks table
    db.run(`
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(20) DEFAULT 'todo' CHECK (status IN ('todo', 'in_progress', 'done')),
            priority VARCHAR(10) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
            assigned_to VARCHAR(255),
            project_id INTEGER,
            due_date DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
        )
    `);

    // Check if data already exists
    db.get("SELECT COUNT(*) as count FROM projects", (err, row) => {
        if (err) {
            console.error('Error checking projects:', err);
            return;
        }
        
        if (row.count === 0) {
            // Insert sample projects
            const projectStmt = db.prepare("INSERT INTO projects (name, description) VALUES (?, ?)");
            projectStmt.run("Website Redesign", "Complete redesign of the company website with modern UI/UX");
            projectStmt.run("Mobile App Development", "Develop a mobile application for iOS and Android platforms");
            projectStmt.run("Database Migration", "Migrate existing database to new cloud infrastructure");
            projectStmt.finalize();

            // Insert sample tasks
            const taskStmt = db.prepare("INSERT INTO tasks (title, description, status, priority, assigned_to, project_id, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            taskStmt.run("Design Homepage Mockup", "Create initial mockup for the new homepage design", "todo", "high", "john.doe@example.com", 1, "2025-07-20");
            taskStmt.run("Setup Development Environment", "Configure development environment for mobile app", "in_progress", "medium", "jane.smith@example.com", 2, "2025-07-15");
            taskStmt.run("Database Schema Design", "Design new database schema for migration", "done", "high", "bob.wilson@example.com", 3, "2025-07-10");
            taskStmt.run("User Authentication System", "Implement user login and registration", "todo", "high", "alice.johnson@example.com", 2, "2025-07-25");
            taskStmt.run("Content Management System", "Develop CMS for website content", "in_progress", "medium", "charlie.brown@example.com", 1, "2025-07-30");
            taskStmt.finalize();
        }
    });
});

// Helper function to render layout
function renderLayout(content, title = 'Task Manager') {
    return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/styles.css">
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-tasks"></i> Task Manager
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tasks">
                            <i class="fas fa-list"></i> Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/projects">
                            <i class="fas fa-folder"></i> Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/database/status">
                            <i class="fas fa-database"></i> Database
                        </a>
                    </li>
                </ul>
                
                <div class="navbar-nav">
                    <div class="nav-item">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" name="q" placeholder="Search tasks..." aria-label="Search"
                                   hx-get="/api/tasks/search" 
                                   hx-target="#search-results" 
                                   hx-trigger="keyup changed delay:500ms">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="container mt-4">
        ${content}
    </main>
    
    <!-- Search Results Container -->
    <div id="search-results" class="search-results"></div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/js/app.js"></script>
</body>
</html>
    `;
}

// Helper function to render task card
function renderTaskCard(task) {
    const statusClass = task.status === 'done' ? 'success' : (task.status === 'in_progress' ? 'info' : 'warning');
    const priorityClass = task.priority === 'high' ? 'danger' : (task.priority === 'medium' ? 'warning' : 'secondary');
    
    return `
        <div class="card task-card h-100" data-task-id="${task.id}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title">${task.title}</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/tasks/${task.id}/edit">
                                <i class="fas fa-edit"></i> Edit
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask(${task.id})">
                                <i class="fas fa-trash"></i> Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
                
                ${task.description ? `<p class="card-text text-muted small">${task.description.substring(0, 100)}${task.description.length > 100 ? '...' : ''}</p>` : ''}
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-${statusClass}">
                            ${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}
                        </span>
                        <span class="badge bg-${priorityClass}">
                            ${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}
                        </span>
                    </div>
                    
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                hx-post="/api/tasks/${task.id}/status"
                                hx-vals='{"status": "todo"}'
                                hx-target="closest .task-card"
                                hx-swap="outerHTML"
                                title="Set to To Do">
                            <i class="fas fa-clipboard-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                hx-post="/api/tasks/${task.id}/status"
                                hx-vals='{"status": "in_progress"}'
                                hx-target="closest .task-card"
                                hx-swap="outerHTML"
                                title="Set to In Progress">
                            <i class="fas fa-spinner"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                hx-post="/api/tasks/${task.id}/status"
                                hx-vals='{"status": "done"}'
                                hx-target="closest .task-card"
                                hx-swap="outerHTML"
                                title="Set to Done">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                
                <div class="small text-muted">
                    ${task.project_name ? `<i class="fas fa-folder"></i> ${task.project_name}<br>` : ''}
                    ${task.assigned_to ? `<i class="fas fa-user"></i> ${task.assigned_to}<br>` : ''}
                    ${task.due_date ? `<i class="fas fa-calendar"></i> Due: ${new Date(task.due_date).toLocaleDateString()}<br>` : ''}
                </div>
            </div>
        </div>
    `;
}

// Routes
app.get('/', (req, res) => {
    res.redirect('/dashboard');
});

app.get('/dashboard', (req, res) => {
    // Get task statistics
    db.get(`
        SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
            SUM(CASE WHEN due_date < date('now') AND status != 'done' THEN 1 ELSE 0 END) as overdue_count
        FROM tasks
    `, (err, taskStats) => {
        if (err) {
            console.error('Error getting task stats:', err);
            return res.status(500).send('Database error');
        }

        // Get recent tasks
        db.all(`
            SELECT t.*, p.name as project_name 
            FROM tasks t 
            LEFT JOIN projects p ON t.project_id = p.id 
            ORDER BY t.created_at DESC 
            LIMIT 5
        `, (err, recentTasks) => {
            if (err) {
                console.error('Error getting recent tasks:', err);
                return res.status(500).send('Database error');
            }

            // Get project count
            db.get("SELECT COUNT(*) as count FROM projects", (err, projectCount) => {
                if (err) {
                    console.error('Error getting project count:', err);
                    return res.status(500).send('Database error');
                }

                const content = `
                    <div class="row">
                        <div class="col-12">
                            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                            <p class="lead">Welcome to your task management dashboard</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4" id="stats-cards" 
                         hx-get="/api/dashboard/stats" 
                         hx-trigger="load, every 30s">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>${taskStats.total_tasks}</h4>
                                            <p>Total Tasks</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tasks fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>${taskStats.todo_count}</h4>
                                            <p>To Do</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clipboard-list fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>${taskStats.in_progress_count}</h4>
                                            <p>In Progress</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-spinner fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4>${taskStats.done_count}</h4>
                                            <p>Completed</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${taskStats.overdue_count > 0 ? `
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> You have ${taskStats.overdue_count} overdue task(s).
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Recent Tasks and Quick Actions -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-history"></i> Recent Tasks</h5>
                                </div>
                                <div class="card-body">
                                    ${recentTasks.length === 0 ? `
                                        <p class="text-muted">No tasks found. <a href="/tasks/create">Create your first task</a></p>
                                    ` : `
                                        <div class="list-group list-group-flush">
                                            ${recentTasks.map(task => `
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">${task.title}</h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> ${new Date(task.created_at).toLocaleDateString()}
                                                            ${task.project_name ? `| <i class="fas fa-folder"></i> ${task.project_name}` : ''}
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-${task.status === 'done' ? 'success' : (task.status === 'in_progress' ? 'info' : 'warning')}">
                                                            ${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}
                                                        </span>
                                                        <span class="badge bg-${task.priority === 'high' ? 'danger' : (task.priority === 'medium' ? 'warning' : 'secondary')}">
                                                            ${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}
                                                        </span>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    `}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-plus"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="/tasks/create" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Create New Task
                                        </a>
                                        <a href="/projects/create" class="btn btn-success">
                                            <i class="fas fa-folder-plus"></i> Create New Project
                                        </a>
                                        <a href="/tasks/kanban" class="btn btn-info">
                                            <i class="fas fa-columns"></i> View Kanban Board
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-pie"></i> Project Summary</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Total Projects:</strong> ${projectCount.count}</p>
                                    <a href="/projects" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-folder"></i> View All Projects
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                res.send(renderLayout(content, 'Dashboard - Task Manager'));
            });
        });
    });
});

app.get('/tasks', (req, res) => {
    db.all(`
        SELECT t.*, p.name as project_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        ORDER BY t.created_at DESC
    `, (err, tasks) => {
        if (err) {
            console.error('Error getting tasks:', err);
            return res.status(500).send('Database error');
        }

        db.all("SELECT * FROM projects ORDER BY name ASC", (err, projects) => {
            if (err) {
                console.error('Error getting projects:', err);
                return res.status(500).send('Database error');
            }

            const content = `
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1><i class="fas fa-list"></i> Tasks</h1>
                            <a href="/tasks/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Task
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tasks Container -->
                <div id="tasks-container">
                    ${tasks.length === 0 ? `
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-5x text-muted mb-3"></i>
                            <h3>No tasks found</h3>
                            <p class="text-muted">Start by creating your first task!</p>
                            <a href="/tasks/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Task
                            </a>
                        </div>
                    ` : `
                        <div class="row">
                            ${tasks.map(task => `
                                <div class="col-md-6 col-lg-4 mb-3">
                                    ${renderTaskCard(task)}
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>

                <!-- View Toggle -->
                <div class="fixed-bottom p-3">
                    <div class="d-flex justify-content-end">
                        <a href="/tasks/kanban" class="btn btn-info">
                            <i class="fas fa-columns"></i> Kanban View
                        </a>
                    </div>
                </div>
            `;

            res.send(renderLayout(content, 'Tasks - Task Manager'));
        });
    });
});

app.get('/tasks/kanban', (req, res) => {
    db.all(`
        SELECT t.*, p.name as project_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        ORDER BY t.priority DESC, t.created_at ASC
    `, (err, tasks) => {
        if (err) {
            console.error('Error getting tasks:', err);
            return res.status(500).send('Database error');
        }

        // Group tasks by status
        const tasksByStatus = {
            todo: [],
            in_progress: [],
            done: []
        };

        tasks.forEach(task => {
            tasksByStatus[task.status].push(task);
        });

        const content = `
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1><i class="fas fa-columns"></i> Kanban Board</h1>
                        <div>
                            <a href="/tasks" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-list"></i> List View
                            </a>
                            <a href="/tasks/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Task
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">To Do</h5>
                        </div>
                        <div class="card-body kanban-column" data-status="todo">
                            ${tasksByStatus.todo.map(task => renderTaskCard(task)).join('')}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">In Progress</h5>
                        </div>
                        <div class="card-body kanban-column" data-status="in_progress">
                            ${tasksByStatus.in_progress.map(task => renderTaskCard(task)).join('')}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Done</h5>
                        </div>
                        <div class="card-body kanban-column" data-status="done">
                            ${tasksByStatus.done.map(task => renderTaskCard(task)).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;

        res.send(renderLayout(content, 'Kanban Board - Task Manager'));
    });
});

app.get('/projects', (req, res) => {
    db.all(`
        SELECT p.*, COUNT(t.id) as task_count 
        FROM projects p 
        LEFT JOIN tasks t ON p.id = t.project_id 
        GROUP BY p.id 
        ORDER BY p.created_at DESC
    `, (err, projects) => {
        if (err) {
            console.error('Error getting projects:', err);
            return res.status(500).send('Database error');
        }

        const content = `
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1><i class="fas fa-folder"></i> Projects</h1>
                        <a href="/projects/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Project
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                ${projects.length === 0 ? `
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-folder fa-5x text-muted mb-3"></i>
                            <h3>No projects found</h3>
                            <p class="text-muted">Organize your tasks by creating your first project!</p>
                            <a href="/projects/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Project
                            </a>
                        </div>
                    </div>
                ` : projects.map(project => `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card project-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title">${project.name}</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="/projects/${project.id}">
                                                <i class="fas fa-eye"></i> View
                                            </a></li>
                                            <li><a class="dropdown-item" href="/projects/${project.id}/edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteProject(${project.id})">
                                                <i class="fas fa-trash"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                ${project.description ? `<p class="card-text text-muted">${project.description.substring(0, 150)}${project.description.length > 150 ? '...' : ''}</p>` : ''}
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary">
                                            ${project.task_count} Task${project.task_count != 1 ? 's' : ''}
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        Created: ${new Date(project.created_at).toLocaleDateString()}
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/projects/${project.id}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Tasks
                                    </a>
                                    <a href="/tasks/create?project_id=${project.id}" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus"></i> Add Task
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        res.send(renderLayout(content, 'Projects - Task Manager'));
    });
});

// API endpoints for HTMX
app.post('/api/tasks/:id/status', (req, res) => {
    const { id } = req.params;
    const { status } = req.body;
    
    db.run("UPDATE tasks SET status = ? WHERE id = ?", [status, id], function(err) {
        if (err) {
            console.error('Error updating task status:', err);
            return res.status(500).json({ error: 'Database error' });
        }
        
        // Get updated task data and return the rendered task card
        db.get("SELECT t.*, p.name as project_name FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE t.id = ?", [id], (err, task) => {
            if (err) {
                console.error('Error getting updated task:', err);
                return res.status(500).json({ error: 'Database error' });
            }
            
            res.send(renderTaskCard(task));
        });
    });
});

app.get('/api/dashboard/stats', (req, res) => {
    db.get(`
        SELECT 
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
            SUM(CASE WHEN due_date < date('now') AND status != 'done' THEN 1 ELSE 0 END) as overdue_count
        FROM tasks
    `, (err, stats) => {
        if (err) {
            console.error('Error getting dashboard stats:', err);
            return res.status(500).json({ error: 'Database error' });
        }
        
        // Return HTML for stats cards
        const statsHtml = `
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>${stats.total_tasks}</h4>
                                <p>Total Tasks</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>${stats.todo_count}</h4>
                                <p>To Do</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>${stats.in_progress_count}</h4>
                                <p>In Progress</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-spinner fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4>${stats.done_count}</h4>
                                <p>Completed</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        res.send(statsHtml);
    });
});

app.get('/api/tasks/search', (req, res) => {
    const { q } = req.query;
    
    if (!q || q.trim() === '') {
        return res.send('');
    }
    
    db.all(`
        SELECT t.*, p.name as project_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        WHERE t.title LIKE ? OR t.description LIKE ?
        ORDER BY t.created_at DESC
    `, [`%${q}%`, `%${q}%`], (err, tasks) => {
        if (err) {
            console.error('Error searching tasks:', err);
            return res.status(500).send('Search error');
        }
        
        const searchResults = tasks.map(task => `
            <div class="search-result-item p-2 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">${task.title}</h6>
                        <small class="text-muted">
                            ${task.project_name ? `<i class="fas fa-folder"></i> ${task.project_name} | ` : ''}
                            <span class="badge bg-${task.status === 'done' ? 'success' : (task.status === 'in_progress' ? 'info' : 'warning')}">${task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('_', ' ')}</span>
                        </small>
                    </div>
                    <a href="/tasks/${task.id}/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                </div>
            </div>
        `).join('');
        
        res.send(searchResults);
    });
});

// Database admin routes
app.get('/database/status', (req, res) => {
    const statusPage = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Status - Task Manager</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1><i class="fas fa-database"></i> Database Status</h1>
                        <div>
                            <a href="/database/admin" class="btn btn-secondary me-2">
                                <i class="fas fa-cogs"></i> Admin Panel
                            </a>
                            <a href="/" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to App
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-server"></i> Database Status</h5>
                                </div>
                                <div class="card-body">
                                    <div id="database-status">
                                        <p><i class="fas fa-spinner fa-spin"></i> Loading database status...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-chart-bar"></i> Database Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div id="db-stats">
                                        <p><i class="fas fa-spinner fa-spin"></i> Loading statistics...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-info-circle"></i> Database Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>Database Type</h6>
                                            <p class="text-muted">SQLite (Development)</p>
                                            <p class="text-muted">PostgreSQL (Production Ready)</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Features</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success"></i> Automatic Schema Creation</li>
                                                <li><i class="fas fa-check text-success"></i> Sample Data Loading</li>
                                                <li><i class="fas fa-check text-success"></i> Database Migrations</li>
                                                <li><i class="fas fa-check text-success"></i> Connection Testing</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>Tables</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-table"></i> projects</li>
                                                <li><i class="fas fa-table"></i> tasks</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Load database status
            fetch('/api/database/test')
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('database-status');
                    const statusClass = data.status === 'success' ? 'success' : 'danger';
                    const statusIcon = data.status === 'success' ? 'check-circle' : 'exclamation-triangle';
                    
                    statusDiv.innerHTML = \`
                        <div class="alert alert-\${statusClass}">
                            <i class="fas fa-\${statusIcon}"></i> \${data.message}
                        </div>
                        <p><strong>Database Type:</strong> \${data.database_type.toUpperCase()}</p>
                        <p><strong>Timestamp:</strong> \${new Date(data.timestamp).toLocaleString()}</p>
                    \`;
                })
                .catch(error => {
                    document.getElementById('database-status').innerHTML = \`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error loading database status: \${error.message}
                        </div>
                    \`;
                });
            
            // Load database statistics
            fetch('/api/database/stats')
                .then(response => response.json())
                .then(data => {
                    const statsDiv = document.getElementById('db-stats');
                    
                    statsDiv.innerHTML = \`
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4>\${data.total_projects}</h4>
                                        <p class="mb-0">Projects</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4>\${data.total_tasks}</h4>
                                        <p class="mb-0">Tasks</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>\${data.todo_count}</h5>
                                        <p class="mb-0">Todo</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>\${data.in_progress_count}</h5>
                                        <p class="mb-0">In Progress</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>\${data.done_count}</h5>
                                        <p class="mb-0">Done</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    \`;
                })
                .catch(error => {
                    document.getElementById('db-stats').innerHTML = \`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error loading database statistics: \${error.message}
                        </div>
                    \`;
                });
        </script>
    </body>
    </html>
    `;
    
    res.send(statusPage);
});

// API endpoint for database statistics
app.get('/api/database/stats', (req, res) => {
    db.serialize(() => {
        db.get("SELECT COUNT(*) as count FROM projects", (err, projectCount) => {
            if (err) {
                console.error('Error getting project count:', err);
                return res.status(500).json({ error: 'Database error' });
            }
            
            db.get("SELECT COUNT(*) as count FROM tasks", (err, taskCount) => {
                if (err) {
                    console.error('Error getting task count:', err);
                    return res.status(500).json({ error: 'Database error' });
                }
                
                db.all("SELECT status, COUNT(*) as count FROM tasks GROUP BY status", (err, statusCounts) => {
                    if (err) {
                        console.error('Error getting status counts:', err);
                        return res.status(500).json({ error: 'Database error' });
                    }
                    
                    db.all("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority", (err, priorityCounts) => {
                        if (err) {
                            console.error('Error getting priority counts:', err);
                            return res.status(500).json({ error: 'Database error' });
                        }
                        
                        const stats = {
                            database_type: 'sqlite',
                            total_projects: projectCount.count,
                            total_tasks: taskCount.count,
                            todo_count: statusCounts.find(s => s.status === 'todo')?.count || 0,
                            in_progress_count: statusCounts.find(s => s.status === 'in_progress')?.count || 0,
                            done_count: statusCounts.find(s => s.status === 'done')?.count || 0,
                            high_priority_count: priorityCounts.find(p => p.priority === 'high')?.count || 0,
                            medium_priority_count: priorityCounts.find(p => p.priority === 'medium')?.count || 0,
                            low_priority_count: priorityCounts.find(p => p.priority === 'low')?.count || 0
                        };
                        
                        res.json(stats);
                    });
                });
            });
        });
    });
});

// API endpoint for database connection test
app.get('/api/database/test', (req, res) => {
    db.get("SELECT 1 as test", (err, result) => {
        if (err) {
            res.status(500).json({
                status: 'error',
                database_type: 'sqlite',
                message: 'Database connection failed: ' + err.message,
                timestamp: new Date().toISOString()
            });
        } else {
            res.json({
                status: 'success',
                database_type: 'sqlite',
                message: 'Database connection successful',
                timestamp: new Date().toISOString()
            });
        }
    });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Task Manager server running on http://0.0.0.0:${PORT}`);
});