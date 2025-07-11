<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Status - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-database"></i> Database Status</h1>
                    <div>
                        <a href="admin.php" class="btn btn-secondary me-2">
                            <i class="fas fa-cogs"></i> Admin Panel
                        </a>
                        <a href="../" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to App
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-server"></i> SQLite Database Status</h5>
                            </div>
                            <div class="card-body">
                                <div id="sqlite-status">
                                    <p><i class="fas fa-spinner fa-spin"></i> Loading database status...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar"></i> Database Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div id="db-stats">
                                    <p><i class="fas fa-spinner fa-spin"></i> Loading statistics...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle"></i> Database Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Database Type</h6>
                                        <p class="text-muted">SQLite (Development)</p>
                                        <p class="text-muted">PostgreSQL (Production Ready)</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Features</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Automatic Schema Creation</li>
                                            <li><i class="fas fa-check text-success"></i> Sample Data Loading</li>
                                            <li><i class="fas fa-check text-success"></i> Database Migrations</li>
                                            <li><i class="fas fa-check text-success"></i> Connection Testing</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Tables</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-table"></i> projects</li>
                                            <li><i class="fas fa-table"></i> tasks</li>
                                            <li><i class="fas fa-table"></i> migrations</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load database status
        fetch('/api/database/test')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('sqlite-status');
                const statusClass = data.status === 'success' ? 'success' : 'danger';
                const statusIcon = data.status === 'success' ? 'check-circle' : 'exclamation-triangle';
                
                statusDiv.innerHTML = `
                    <div class="alert alert-${statusClass}">
                        <i class="fas fa-${statusIcon}"></i> ${data.message}
                    </div>
                    <p><strong>Database Type:</strong> ${data.database_type.toUpperCase()}</p>
                    <p><strong>Timestamp:</strong> ${new Date(data.timestamp).toLocaleString()}</p>
                `;
            })
            .catch(error => {
                document.getElementById('sqlite-status').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error loading database status: ${error.message}
                    </div>
                `;
            });
        
        // Load database statistics
        fetch('/api/database/stats')
            .then(response => response.json())
            .then(data => {
                const statsDiv = document.getElementById('db-stats');
                
                statsDiv.innerHTML = `
                    <div class="row">
                        <div class="col-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>${data.total_projects}</h4>
                                    <p class="mb-0">Projects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>${data.total_tasks}</h4>
                                    <p class="mb-0">Tasks</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>${data.todo_count}</h5>
                                    <p class="mb-0">Todo</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>${data.in_progress_count}</h5>
                                    <p class="mb-0">In Progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>${data.done_count}</h5>
                                    <p class="mb-0">Done</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                document.getElementById('db-stats').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error loading database statistics: ${error.message}
                    </div>
                `;
            });
    </script>
</body>
</html>