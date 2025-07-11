<div class="card task-card h-100" data-task-id="<?php echo $task['id']; ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/tasks/<?php echo $task['id']; ?>/edit">
                        <i class="fas fa-edit"></i> Edit
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask(<?php echo $task['id']; ?>)">
                        <i class="fas fa-trash"></i> Delete
                    </a></li>
                </ul>
            </div>
        </div>
        
        <?php if ($task['description']): ?>
            <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($task['description'], 0, 100)) . (strlen($task['description']) > 100 ? '...' : ''); ?></p>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="badge bg-<?php echo $task['status'] === 'done' ? 'success' : ($task['status'] === 'in_progress' ? 'info' : 'warning'); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                </span>
                <span class="badge bg-<?php echo $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary'); ?>">
                    <?php echo ucfirst($task['priority']); ?>
                </span>
            </div>
            
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary" 
                        hx-post="/tasks/<?php echo $task['id']; ?>/status" 
                        hx-vals='{"status": "todo"}'
                        hx-target="closest .task-card"
                        title="Set to To Do">
                    <i class="fas fa-clipboard-list"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" 
                        hx-post="/tasks/<?php echo $task['id']; ?>/status" 
                        hx-vals='{"status": "in_progress"}'
                        hx-target="closest .task-card"
                        title="Set to In Progress">
                    <i class="fas fa-spinner"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" 
                        hx-post="/tasks/<?php echo $task['id']; ?>/status" 
                        hx-vals='{"status": "done"}'
                        hx-target="closest .task-card"
                        title="Set to Done">
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
        
        <div class="small text-muted">
            <?php if ($task['project_name']): ?>
                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($task['project_name']); ?><br>
            <?php endif; ?>
            <?php if ($task['assigned_to']): ?>
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($task['assigned_to']); ?><br>
            <?php endif; ?>
            <?php if ($task['due_date']): ?>
                <i class="fas fa-calendar"></i> Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                <?php if (strtotime($task['due_date']) < time() && $task['status'] !== 'done'): ?>
                    <span class="badge bg-danger ms-1">Overdue</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
