<?php
class ProjectController {
    private $db;
    private $project;
    
    public function __construct($db) {
        $this->db = $db;
        $this->project = new Project($db);
    }
    
    public function index() {
        $stmt = $this->project->read();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include 'views/projects/index.php';
    }
    
    public function create() {
        include 'views/projects/create.php';
    }
    
    public function store() {
        if ($_POST) {
            $this->project->name = $_POST['name'];
            $this->project->description = $_POST['description'];
            
            if ($this->project->create()) {
                header('Location: /projects');
                exit;
            } else {
                $error = "Unable to create project.";
            }
        }
        
        header('Location: /projects/create');
        exit;
    }
    
    public function show($id) {
        $this->project->id = $id;
        $project = $this->project->readOne();
        
        if (!$project) {
            header('HTTP/1.0 404 Not Found');
            echo "Project not found";
            return;
        }
        
        // Get tasks for this project
        $task = new Task($this->db);
        $stmt = $task->filter(['project_id' => $id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include 'views/projects/show.php';
    }
    
    public function edit($id) {
        $this->project->id = $id;
        $project = $this->project->readOne();
        
        if (!$project) {
            header('HTTP/1.0 404 Not Found');
            echo "Project not found";
            return;
        }
        
        include 'views/projects/edit.php';
    }
    
    public function update($id) {
        if ($_POST) {
            $this->project->id = $id;
            $this->project->name = $_POST['name'];
            $this->project->description = $_POST['description'];
            
            if ($this->project->update()) {
                header('Location: /projects');
                exit;
            } else {
                $error = "Unable to update project.";
            }
        }
        
        header('Location: /projects/' . $id . '/edit');
        exit;
    }
    
    public function delete($id) {
        $this->project->id = $id;
        
        if ($this->project->delete()) {
            header('Location: /projects');
            exit;
        } else {
            echo "Unable to delete project.";
        }
    }
}
?>
