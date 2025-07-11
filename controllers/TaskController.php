<?php
class TaskController {
    private $db;
    private $task;
    
    public function __construct($db) {
        $this->db = $db;
        $this->task = new Task($db);
    }
    
    public function index() {
        $stmt = $this->task->read();
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects for filter dropdown
        $project = new Project($this->db);
        $projects = $project->getAll();
        
        include 'views/tasks/index.php';
    }
    
    public function create() {
        // Get projects for dropdown
        $project = new Project($this->db);
        $projects = $project->getAll();
        
        include 'views/tasks/create.php';
    }
    
    public function store() {
        if ($_POST) {
            $this->task->title = $_POST['title'];
            $this->task->description = $_POST['description'];
            $this->task->status = $_POST['status'];
            $this->task->priority = $_POST['priority'];
            $this->task->assigned_to = $_POST['assigned_to'];
            $this->task->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
            $this->task->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            
            if ($this->task->create()) {
                header('Location: /tasks');
                exit;
            } else {
                $error = "Unable to create task.";
            }
        }
        
        // If error, redirect back to create form
        header('Location: /tasks/create');
        exit;
    }
    
    public function show($id) {
        $this->task->id = $id;
        $task = $this->task->readOne();
        
        if (!$task) {
            header('HTTP/1.0 404 Not Found');
            echo "Task not found";
            return;
        }
        
        include 'views/tasks/show.php';
    }
    
    public function edit($id) {
        $this->task->id = $id;
        $task = $this->task->readOne();
        
        if (!$task) {
            header('HTTP/1.0 404 Not Found');
            echo "Task not found";
            return;
        }
        
        // Get projects for dropdown
        $project = new Project($this->db);
        $projects = $project->getAll();
        
        include 'views/tasks/edit.php';
    }
    
    public function update($id) {
        if ($_POST) {
            $this->task->id = $id;
            $this->task->title = $_POST['title'];
            $this->task->description = $_POST['description'];
            $this->task->status = $_POST['status'];
            $this->task->priority = $_POST['priority'];
            $this->task->assigned_to = $_POST['assigned_to'];
            $this->task->project_id = !empty($_POST['project_id']) ? $_POST['project_id'] : null;
            $this->task->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            
            if ($this->task->update()) {
                header('Location: /tasks');
                exit;
            } else {
                $error = "Unable to update task.";
            }
        }
        
        header('Location: /tasks/' . $id . '/edit');
        exit;
    }
    
    public function delete($id) {
        $this->task->id = $id;
        
        if ($this->task->delete()) {
            header('Location: /tasks');
            exit;
        } else {
            echo "Unable to delete task.";
        }
    }
    
    public function updateStatus($id) {
        if ($_POST && isset($_POST['status'])) {
            $this->task->id = $id;
            $this->task->status = $_POST['status'];
            
            if ($this->task->updateStatus()) {
                // Return updated task card for HTMX
                $task = $this->task->readOne();
                include 'views/components/task-card.php';
            } else {
                echo "Unable to update task status.";
            }
        }
    }
    
    public function search() {
        $keyword = $_GET['q'] ?? '';
        
        if (!empty($keyword)) {
            $stmt = $this->task->search($keyword);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $tasks = [];
        }
        
        // Return task list for HTMX
        foreach ($tasks as $task) {
            include 'views/components/task-card.php';
        }
    }
    
    public function filter() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'project_id' => $_GET['project_id'] ?? ''
        ];
        
        $stmt = $this->task->filter($filters);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return task list for HTMX
        foreach ($tasks as $task) {
            include 'views/components/task-card.php';
        }
    }
    
    public function kanban() {
        $tasks = $this->task->getKanbanTasks();
        
        $statuses = ['todo', 'in_progress', 'done'];
        
        echo '<div class="row">';
        foreach ($statuses as $status) {
            $status_name = ucfirst(str_replace('_', ' ', $status));
            echo '<div class="col-md-4">';
            echo '<div class="card">';
            echo '<div class="card-header">';
            echo '<h5 class="card-title">' . $status_name . '</h5>';
            echo '</div>';
            echo '<div class="card-body kanban-column" data-status="' . $status . '">';
            
            if (isset($tasks[$status])) {
                foreach ($tasks[$status] as $task) {
                    include 'views/components/task-card.php';
                }
            }
            
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?>
