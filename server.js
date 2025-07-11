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
                </ul>
                
                <div class="navbar-nav">
                    <div class="nav-item">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" name="q" placeholder="Search tasks..." aria-label="Search">
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
                                onclick="updateTaskStatus(${task.id}, 'todo')"
                                title="Set to To Do">
                            <i class="fas fa-clipboard-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="updateTaskStatus(${task.id}, 'in_progress')"
                                title="Set to In Progress">
                            <i class="fas fa-spinner"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="updateTaskStatus(${task.id}, 'done')"
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
                    <div class="row mb-4">
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

// API endpoints for status updates
app.post('/api/tasks/:id/status', (req, res) => {
    const { id } = req.params;
    const { status } = req.body;
    
    db.run("UPDATE tasks SET status = ? WHERE id = ?", [status, id], function(err) {
        if (err) {
            console.error('Error updating task status:', err);
            return res.status(500).json({ error: 'Database error' });
        }
        
        res.json({ success: true });
    });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Task Manager server running on http://0.0.0.0:${PORT}`);
});