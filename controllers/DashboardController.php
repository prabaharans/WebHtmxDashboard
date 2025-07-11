<?php
class DashboardController {
    private $db;
    private $task;
    private $project;
    
    public function __construct($db) {
        $this->db = $db;
        $this->task = new Task($db);
        $this->project = new Project($db);
    }
    
    public function index() {
        // Get task statistics
        $task_stats = $this->task->getStats();
        
        // Get recent tasks
        $stmt = $this->task->read();
        $recent_tasks = array_slice($stmt->fetchAll(PDO::FETCH_ASSOC), 0, 5);
        
        // Get project count
        $stmt = $this->project->read();
        $project_count = $stmt->rowCount();
        
        include 'views/dashboard.php';
    }
    
    public function stats() {
        $stats = $this->task->getStats();
        
        // Return JSON for HTMX
        header('Content-Type: application/json');
        echo json_encode($stats);
    }
}
?>
