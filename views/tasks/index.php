<?php
$title = 'Tasks';
ob_start();
?>

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

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form hx-get="/tasks/filter" hx-target="#tasks-container" hx-trigger="change">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="priority" class="form-select">
                                <option value="">All Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="project_id" class="form-select">
                                <option value="">All Projects</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-secondary" onclick="this.form.reset(); htmx.trigger(this.form, 'change');">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Container -->
<div id="tasks-container">
    <?php if (empty($tasks)): ?>
        <div class="text-center py-5">
            <i class="fas fa-tasks fa-5x text-muted mb-3"></i>
            <h3>No tasks found</h3>
            <p class="text-muted">Start by creating your first task!</p>
            <a href="/tasks/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create First Task
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tasks as $task): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <?php include 'views/components/task-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- View Toggle -->
<div class="fixed-bottom p-3">
    <div class="d-flex justify-content-end">
        <button class="btn btn-info" hx-get="/api/tasks/kanban" hx-target="#tasks-container">
            <i class="fas fa-columns"></i> Kanban View
        </button>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'views/layout.php';
?>
