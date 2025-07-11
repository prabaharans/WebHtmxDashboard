<?php
$title = 'Dashboard';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <p class="lead">Welcome to your task management dashboard</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4" id="stats-cards">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $task_stats['total_tasks']; ?></h4>
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
                        <h4><?php echo $task_stats['todo_count']; ?></h4>
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
                        <h4><?php echo $task_stats['in_progress_count']; ?></h4>
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
                        <h4><?php echo $task_stats['done_count']; ?></h4>
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

<?php if ($task_stats['overdue_count'] > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> You have <?php echo $task_stats['overdue_count']; ?> overdue task(s).
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Tasks and Quick Actions -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Recent Tasks</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_tasks)): ?>
                    <p class="text-muted">No tasks found. <a href="/tasks/create">Create your first task</a></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_tasks as $task): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($task['title']); ?></h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                                        <?php if ($task['project_name']): ?>
                                            | <i class="fas fa-folder"></i> <?php echo htmlspecialchars($task['project_name']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-<?php echo $task['status'] === 'done' ? 'success' : ($task['status'] === 'in_progress' ? 'info' : 'warning'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                    </span>
                                    <span class="badge bg-<?php echo $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst($task['priority']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                    <a href="/api/tasks/kanban" class="btn btn-info" hx-get="/api/tasks/kanban" hx-target="#kanban-board">
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
                <p><strong>Total Projects:</strong> <?php echo $project_count; ?></p>
                <a href="/projects" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-folder"></i> View All Projects
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Kanban Board Container -->
<div id="kanban-board" class="mt-4"></div>

<!-- Auto-refresh stats every 30 seconds -->
<script>
    setInterval(function() {
        htmx.ajax('GET', '/api/dashboard/stats', {
            target: '#stats-cards',
            swap: 'outerHTML'
        });
    }, 30000);
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
