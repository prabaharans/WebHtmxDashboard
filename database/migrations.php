<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Database.php';

class DatabaseMigrations {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function runMigrations() {
        $this->createMigrationsTable();
        
        $migrations = [
            '2025_01_01_000000_create_projects_table',
            '2025_01_01_000001_create_tasks_table',
            '2025_01_01_000002_add_indexes',
            '2025_01_01_000003_add_sample_data'
        ];
        
        foreach ($migrations as $migration) {
            if (!$this->migrationExists($migration)) {
                $this->runMigration($migration);
                $this->recordMigration($migration);
            }
        }
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY " . (DB_TYPE === 'postgresql' ? '' : 'AUTOINCREMENT') . ",
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->getConnection()->exec($sql);
    }
    
    private function migrationExists($migration) {
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function runMigration($migration) {
        switch ($migration) {
            case '2025_01_01_000000_create_projects_table':
                $this->createProjectsTable();
                break;
            case '2025_01_01_000001_create_tasks_table':
                $this->createTasksTable();
                break;
            case '2025_01_01_000002_add_indexes':
                $this->addIndexes();
                break;
            case '2025_01_01_000003_add_sample_data':
                $this->addSampleData();
                break;
        }
    }
    
    private function recordMigration($migration) {
        $stmt = $this->db->getConnection()->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migration]);
    }
    
    private function createProjectsTable() {
        if (DB_TYPE === 'postgresql') {
            $sql = "CREATE TABLE IF NOT EXISTS projects (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
        }
        
        $this->db->getConnection()->exec($sql);
    }
    
    private function createTasksTable() {
        if (DB_TYPE === 'postgresql') {
            $sql = "CREATE TABLE IF NOT EXISTS tasks (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                status VARCHAR(20) DEFAULT 'todo' CHECK (status IN ('todo', 'in_progress', 'done')),
                priority VARCHAR(10) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high')),
                assigned_to VARCHAR(255),
                project_id INTEGER REFERENCES projects(id) ON DELETE SET NULL,
                due_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS tasks (
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
            )";
        }
        
        $this->db->getConnection()->exec($sql);
    }
    
    private function addIndexes() {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_status ON tasks(status)",
            "CREATE INDEX IF NOT EXISTS idx_priority ON tasks(priority)",
            "CREATE INDEX IF NOT EXISTS idx_project_id ON tasks(project_id)",
            "CREATE INDEX IF NOT EXISTS idx_due_date ON tasks(due_date)",
            "CREATE INDEX IF NOT EXISTS idx_assigned_to ON tasks(assigned_to)",
            "CREATE INDEX IF NOT EXISTS idx_tasks_created_at ON tasks(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_tasks_updated_at ON tasks(updated_at)",
            "CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects(created_at)"
        ];
        
        foreach ($indexes as $index) {
            $this->db->getConnection()->exec($index);
        }
    }
    
    private function addSampleData() {
        // Check if data already exists
        $stmt = $this->db->getConnection()->query("SELECT COUNT(*) FROM projects");
        if ($stmt->fetchColumn() > 0) {
            return; // Sample data already exists
        }
        
        // Insert sample projects
        $projects = [
            ['Website Redesign', 'Complete redesign of the company website with modern UI/UX'],
            ['Mobile App Development', 'Develop a mobile application for iOS and Android platforms'],
            ['Database Migration', 'Migrate existing database to new cloud infrastructure']
        ];
        
        $stmt = $this->db->getConnection()->prepare("INSERT INTO projects (name, description) VALUES (?, ?)");
        foreach ($projects as $project) {
            $stmt->execute($project);
        }
        
        // Insert sample tasks
        $tasks = [
            ['Design Homepage Mockup', 'Create initial mockup for the new homepage design', 'todo', 'high', 'john.doe@example.com', 1, '2025-07-20'],
            ['Setup Development Environment', 'Configure development environment for mobile app', 'in_progress', 'medium', 'jane.smith@example.com', 2, '2025-07-15'],
            ['Database Schema Design', 'Design new database schema for migration', 'done', 'high', 'bob.wilson@example.com', 3, '2025-07-10'],
            ['User Authentication System', 'Implement user login and registration', 'todo', 'high', 'alice.johnson@example.com', 2, '2025-07-25'],
            ['Content Management System', 'Develop CMS for website content', 'in_progress', 'medium', 'charlie.brown@example.com', 1, '2025-07-30']
        ];
        
        $stmt = $this->db->getConnection()->prepare("INSERT INTO tasks (title, description, status, priority, assigned_to, project_id, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($tasks as $task) {
            $stmt->execute($task);
        }
    }
    
    public function getStats() {
        $conn = $this->db->getConnection();
        
        $stats = [
            'database_type' => DB_TYPE,
            'total_projects' => $conn->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'total_tasks' => $conn->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
            'tasks_by_status' => [],
            'tasks_by_priority' => [],
            'recent_tasks' => []
        ];
        
        // Get tasks by status
        $statusResult = $conn->query("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
        while ($row = $statusResult->fetch()) {
            $stats['tasks_by_status'][$row['status']] = $row['count'];
        }
        
        // Get tasks by priority
        $priorityResult = $conn->query("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority");
        while ($row = $priorityResult->fetch()) {
            $stats['tasks_by_priority'][$row['priority']] = $row['count'];
        }
        
        // Get recent tasks
        $recentResult = $conn->query("SELECT title, status, created_at FROM tasks ORDER BY created_at DESC LIMIT 5");
        $stats['recent_tasks'] = $recentResult->fetchAll();
        
        return $stats;
    }
}

// Command line interface for migrations
if (php_sapi_name() === 'cli') {
    $migrations = new DatabaseMigrations();
    
    if ($argc > 1) {
        switch ($argv[1]) {
            case 'migrate':
                echo "Running database migrations...\n";
                $migrations->runMigrations();
                echo "Migrations completed successfully!\n";
                break;
                
            case 'stats':
                echo "Database Statistics:\n";
                $stats = $migrations->getStats();
                echo "Database Type: " . $stats['database_type'] . "\n";
                echo "Total Projects: " . $stats['total_projects'] . "\n";
                echo "Total Tasks: " . $stats['total_tasks'] . "\n";
                echo "Tasks by Status:\n";
                foreach ($stats['tasks_by_status'] as $status => $count) {
                    echo "  $status: $count\n";
                }
                echo "Tasks by Priority:\n";
                foreach ($stats['tasks_by_priority'] as $priority => $count) {
                    echo "  $priority: $count\n";
                }
                break;
                
            default:
                echo "Usage: php migrations.php [migrate|stats]\n";
        }
    } else {
        echo "Usage: php migrations.php [migrate|stats]\n";
    }
}
?>