<?php
$title = 'Projects';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-folder"></i> Projects</h1>
            <a href="/projects/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Project
            </a>
        </div>
    </div>
</div>

<div class="row">
    <?php if (empty($projects)): ?>
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-folder fa-5x text-muted mb-3"></i>
                <h3>No projects found</h3>
                <p class="text-muted">Organize your tasks by creating your first project!</p>
                <a href="/projects/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Project
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <?php include 'views/components/project-card.php'; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'views/layout.php';
?>
