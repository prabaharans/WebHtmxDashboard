<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Administration - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-database"></i> Database Administration</h1>
                    <a href="../" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to App
                    </a>
                </div>
                
                <?php
                require_once __DIR__ . '/../config/database.php';
                require_once __DIR__ . '/../models/Database.php';
                require_once __DIR__ . '/migrations.php';
                
                $db = new Database();
                $migrations = new DatabaseMigrations();
                
                // Handle actions
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'test_connection':
                            $testResult = $db->testConnection();
                            break;
                        case 'run_migrations':
                            $migrations->runMigrations();
                            $migrationResult = "Migrations completed successfully!";
                            break;
                        case 'reset_database':
                            if (DB_TYPE === 'sqlite' && file_exists(DB_PATH)) {
                                unlink(DB_PATH);
                                $db = new Database(); // Recreate
                                $resetResult = "Database reset and recreated successfully!";
                            } else {
                                $resetResult = "Database reset not supported for PostgreSQL via web interface.";
                            }
                            break;
                    }
                }
                
                $stats = $db->getStats();
                $connectionTest = $db->testConnection();
                ?>
                
                <!-- Connection Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-plug"></i> Database Connection Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-<?php echo $connectionTest['status'] === 'success' ? 'success' : 'danger'; ?>">
                                    <i class="fas fa-<?php echo $connectionTest['status'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                                    <?php echo $connectionTest['message']; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Database Type:</strong> <?php echo strtoupper($stats['database_type']); ?></p>
                                <?php if (DB_TYPE === 'sqlite'): ?>
                                    <p><strong>Database File:</strong> <?php echo DB_PATH; ?></p>
                                    <p><strong>File Size:</strong> <?php echo file_exists(DB_PATH) ? number_format(filesize(DB_PATH)) . ' bytes' : 'N/A'; ?></p>
                                <?php else: ?>
                                    <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                                    <p><strong>Port:</strong> <?php echo DB_PORT; ?></p>
                                    <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Database Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Database Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $stats['total_projects']; ?></h3>
                                        <p>Total Projects</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $stats['total_tasks']; ?></h3>
                                        <p>Total Tasks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $stats['todo_count']; ?></h3>
                                        <p>Todo Tasks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3><?php echo $stats['done_count']; ?></h3>
                                        <p>Completed Tasks</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Tasks by Status</h6>
                                        <ul class="list-unstyled">
                                            <li>Todo: <?php echo $stats['todo_count']; ?></li>
                                            <li>In Progress: <?php echo $stats['in_progress_count']; ?></li>
                                            <li>Done: <?php echo $stats['done_count']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Tasks by Priority</h6>
                                        <ul class="list-unstyled">
                                            <li>High: <?php echo $stats['high_priority_count']; ?></li>
                                            <li>Medium: <?php echo $stats['medium_priority_count']; ?></li>
                                            <li>Low: <?php echo $stats['low_priority_count']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Database Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-tools"></i> Database Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <form method="post" class="mb-3">
                                    <input type="hidden" name="action" value="test_connection">
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                </form>
                                <?php if (isset($testResult)): ?>
                                    <div class="alert alert-<?php echo $testResult['status'] === 'success' ? 'success' : 'danger'; ?>">
                                        <?php echo $testResult['message']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <form method="post" class="mb-3">
                                    <input type="hidden" name="action" value="run_migrations">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-database"></i> Run Migrations
                                    </button>
                                </form>
                                <?php if (isset($migrationResult)): ?>
                                    <div class="alert alert-success">
                                        <?php echo $migrationResult; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <form method="post" class="mb-3" onsubmit="return confirm('Are you sure you want to reset the database? This will delete all data!')">
                                    <input type="hidden" name="action" value="reset_database">
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> Reset Database
                                    </button>
                                </form>
                                <?php if (isset($resetResult)): ?>
                                    <div class="alert alert-info">
                                        <?php echo $resetResult; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SQL Query Interface -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-terminal"></i> SQL Query Interface</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="sql_query" class="form-label">Enter SQL Query:</label>
                                <textarea class="form-control" id="sql_query" name="sql_query" rows="4" placeholder="SELECT * FROM tasks;"><?php echo isset($_POST['sql_query']) ? htmlspecialchars($_POST['sql_query']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="action" value="execute_query" class="btn btn-primary">
                                <i class="fas fa-play"></i> Execute Query
                            </button>
                        </form>
                        
                        <?php if (isset($_POST['action']) && $_POST['action'] === 'execute_query' && !empty($_POST['sql_query'])): ?>
                            <div class="mt-4">
                                <h6>Query Results:</h6>
                                <?php
                                try {
                                    $query = trim($_POST['sql_query']);
                                    $stmt = $db->getConnection()->prepare($query);
                                    $stmt->execute();
                                    
                                    if (stripos($query, 'SELECT') === 0) {
                                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        if (!empty($results)) {
                                            echo '<div class="table-responsive">';
                                            echo '<table class="table table-striped">';
                                            echo '<thead><tr>';
                                            foreach (array_keys($results[0]) as $column) {
                                                echo '<th>' . htmlspecialchars($column) . '</th>';
                                            }
                                            echo '</tr></thead><tbody>';
                                            foreach ($results as $row) {
                                                echo '<tr>';
                                                foreach ($row as $value) {
                                                    echo '<td>' . htmlspecialchars($value) . '</td>';
                                                }
                                                echo '</tr>';
                                            }
                                            echo '</tbody></table>';
                                            echo '</div>';
                                        } else {
                                            echo '<div class="alert alert-info">No results found.</div>';
                                        }
                                    } else {
                                        $rowCount = $stmt->rowCount();
                                        echo '<div class="alert alert-success">Query executed successfully. ' . $rowCount . ' row(s) affected.</div>';
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>