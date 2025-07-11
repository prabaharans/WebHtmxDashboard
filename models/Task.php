<?php
class Task {
    private $conn;
    private $table = "tasks";
    
    public $id;
    public $title;
    public $description;
    public $status;
    public $priority;
    public $assigned_to;
    public $project_id;
    public $due_date;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET title = :title, 
                      description = :description, 
                      status = :status, 
                      priority = :priority, 
                      assigned_to = :assigned_to, 
                      project_id = :project_id, 
                      due_date = :due_date";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->assigned_to = htmlspecialchars(strip_tags($this->assigned_to));
        
        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":due_date", $this->due_date);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    public function read() {
        $query = "SELECT t.*, p.name as project_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN projects p ON t.project_id = p.id 
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    public function readOne() {
        $query = "SELECT t.*, p.name as project_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN projects p ON t.project_id = p.id 
                  WHERE t.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->priority = $row['priority'];
            $this->assigned_to = $row['assigned_to'];
            $this->project_id = $row['project_id'];
            $this->due_date = $row['due_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
        }
        
        return $row;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, 
                      description = :description, 
                      status = :status, 
                      priority = :priority, 
                      assigned_to = :assigned_to, 
                      project_id = :project_id, 
                      due_date = :due_date 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->assigned_to = htmlspecialchars(strip_tags($this->assigned_to));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    public function updateStatus() {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }
    
    public function search($keyword) {
        $query = "SELECT t.*, p.name as project_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN projects p ON t.project_id = p.id 
                  WHERE t.title LIKE :keyword OR t.description LIKE :keyword 
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        
        return $stmt;
    }
    
    public function filter($filters) {
        $query = "SELECT t.*, p.name as project_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN projects p ON t.project_id = p.id 
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $query .= " AND t.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        
        if (!empty($filters['project_id'])) {
            $query .= " AND t.project_id = :project_id";
            $params[':project_id'] = $filters['project_id'];
        }
        
        $query .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt;
    }
    
    public function getKanbanTasks() {
        $query = "SELECT t.*, p.name as project_name 
                  FROM " . $this->table . " t 
                  LEFT JOIN projects p ON t.project_id = p.id 
                  ORDER BY t.priority DESC, t.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[$row['status']][] = $row;
        }
        
        return $tasks;
    }
    
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
                    SUM(CASE WHEN due_date < NOW() AND status != 'done' THEN 1 ELSE 0 END) as overdue_count
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
