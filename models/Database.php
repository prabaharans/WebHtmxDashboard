<?php
class Database {
    private $conn;
    
    public function __construct() {
        $this->initializeDatabase();
    }
    
    private function initializeDatabase() {
        $isNewDb = false;
        
        // Handle SQLite specific setup
        if (DB_TYPE === 'sqlite') {
            // Create database directory if it doesn't exist
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            // Create database file if it doesn't exist
            $isNewDb = !file_exists(DB_PATH);
        }
        
        try {
            $dsn = getDatabaseDSN();
            list($username, $password) = getDatabaseCredentials();
            
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // For PostgreSQL, set schema search path
            if (DB_TYPE === 'postgresql') {
                $this->conn->exec("SET search_path TO public");
            }
            
            // Create tables if new database or if tables don't exist
            if ($isNewDb || !$this->tablesExist()) {
                $this->createTables();
                $this->insertSampleData();
            }
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
    }
    
    private function tablesExist() {
        try {
            if (DB_TYPE === 'postgresql') {
                $result = $this->conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name IN ('projects', 'tasks')");
                return $result->rowCount() >= 2;
            } else {
                $result = $this->conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('projects', 'tasks')");
                return $result->rowCount() >= 2;
            }
        } catch(PDOException $e) {
            return false;
        }
    }
    
    private function createTables() {
        if (DB_TYPE === 'postgresql') {
            $sql = "
                CREATE TABLE IF NOT EXISTS projects (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS tasks (
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
                );
                
                CREATE INDEX IF NOT EXISTS idx_status ON tasks(status);
                CREATE INDEX IF NOT EXISTS idx_priority ON tasks(priority);
                CREATE INDEX IF NOT EXISTS idx_project_id ON tasks(project_id);
                CREATE INDEX IF NOT EXISTS idx_due_date ON tasks(due_date);
                CREATE INDEX IF NOT EXISTS idx_assigned_to ON tasks(assigned_to);
                CREATE INDEX IF NOT EXISTS idx_tasks_created_at ON tasks(created_at);
                CREATE INDEX IF NOT EXISTS idx_tasks_updated_at ON tasks(updated_at);
                CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects(created_at);
            ";
        } else {
            $sql = "
                CREATE TABLE IF NOT EXISTS projects (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                
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
                );
                
                CREATE INDEX IF NOT EXISTS idx_status ON tasks(status);
                CREATE INDEX IF NOT EXISTS idx_priority ON tasks(priority);
                CREATE INDEX IF NOT EXISTS idx_project_id ON tasks(project_id);
                CREATE INDEX IF NOT EXISTS idx_due_date ON tasks(due_date);
                CREATE INDEX IF NOT EXISTS idx_assigned_to ON tasks(assigned_to);
                CREATE INDEX IF NOT EXISTS idx_tasks_created_at ON tasks(created_at);
                CREATE INDEX IF NOT EXISTS idx_tasks_updated_at ON tasks(updated_at);
                CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects(created_at);
            ";
        }
        
        $this->conn->exec($sql);
    }
    
    private function insertSampleData() {
        $sql = "
            INSERT INTO projects (name, description) VALUES
            ('Website Redesign', 'Complete redesign of the company website with modern UI/UX'),
            ('Mobile App Development', 'Develop a mobile application for iOS and Android platforms'),
            ('Database Migration', 'Migrate existing database to new cloud infrastructure');
            
            INSERT INTO tasks (title, description, status, priority, assigned_to, project_id, due_date) VALUES
            ('Design Homepage Mockup', 'Create initial mockup for the new homepage design', 'todo', 'high', 'john.doe@example.com', 1, '2025-07-20'),
            ('Setup Development Environment', 'Configure development environment for mobile app', 'in_progress', 'medium', 'jane.smith@example.com', 2, '2025-07-15'),
            ('Database Schema Design', 'Design new database schema for migration', 'done', 'high', 'bob.wilson@example.com', 3, '2025-07-10'),
            ('User Authentication System', 'Implement user login and registration', 'todo', 'high', 'alice.johnson@example.com', 2, '2025-07-25'),
            ('Content Management System', 'Develop CMS for website content', 'in_progress', 'medium', 'charlie.brown@example.com', 1, '2025-07-30');
        ";
        
        $this->conn->exec($sql);
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function getStats() {
        $stats = [
            'database_type' => DB_TYPE,
            'total_projects' => $this->conn->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
            'total_tasks' => $this->conn->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
            'todo_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'todo'")->fetchColumn(),
            'in_progress_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'in_progress'")->fetchColumn(),
            'done_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'done'")->fetchColumn(),
            'high_priority_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE priority = 'high'")->fetchColumn(),
            'medium_priority_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE priority = 'medium'")->fetchColumn(),
            'low_priority_count' => $this->conn->query("SELECT COUNT(*) FROM tasks WHERE priority = 'low'")->fetchColumn(),
        ];
        
        return $stats;
    }
    
    public function testConnection() {
        try {
            $this->conn->query("SELECT 1");
            return [
                'status' => 'success',
                'database_type' => DB_TYPE,
                'message' => 'Database connection successful'
            ];
        } catch(PDOException $e) {
            return [
                'status' => 'error',
                'database_type' => DB_TYPE,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
}
?>
