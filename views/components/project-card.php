<div class="card project-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/projects/<?php echo $project['id']; ?>">
                        <i class="fas fa-eye"></i> View
                    </a></li>
                    <li><a class="dropdown-item" href="/projects/<?php echo $project['id']; ?>/edit">
                        <i class="fas fa-edit"></i> Edit
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteProject(<?php echo $project['id']; ?>)">
                        <i class="fas fa-trash"></i> Delete
                    </a></li>
                </ul>
            </div>
        </div>
        
        <?php if ($project['description']): ?>
            <p class="card-text text-muted"><?php echo htmlspecialchars(substr($project['description'], 0, 150)) . (strlen($project['description']) > 150 ? '...' : ''); ?></p>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary">
                    <?php echo $project['task_count']; ?> Task<?php echo $project['task_count'] != 1 ? 's' : ''; ?>
                </span>
            </div>
            <small class="text-muted">
                Created: <?php echo date('M j, Y', strtotime($project['created_at'])); ?>
            </small>
        </div>
    </div>
    
    <div class="card-footer">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="/projects/<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View Tasks
            </a>
            <a href="/tasks/create?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Add Task
            </a>
        </div>
    </div>
</div>
