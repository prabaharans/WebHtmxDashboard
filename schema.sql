-- Task Manager Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to VARCHAR(255),
    project_id INT,
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_project_id (project_id),
    INDEX idx_due_date (due_date),
    INDEX idx_assigned_to (assigned_to)
);

-- Insert sample data (optional)
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

-- Create indexes for better performance
CREATE INDEX idx_tasks_created_at ON tasks(created_at);
CREATE INDEX idx_tasks_updated_at ON tasks(updated_at);
CREATE INDEX idx_projects_created_at ON projects(created_at);

-- Create full-text search index for tasks
ALTER TABLE tasks ADD FULLTEXT(title, description);
