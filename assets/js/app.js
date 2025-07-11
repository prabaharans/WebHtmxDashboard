// Task Manager HTMX Application JavaScript

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeHTMXApp();
});

// Main HTMX application initialization
function initializeHTMXApp() {
    initializeKanban();
    initializeFormValidation();
    initializeTooltips();
    initializeModals();
    initializeNotifications();
}

// Kanban Board Functionality
function initializeKanban() {
    const kanbanColumns = document.querySelectorAll('.kanban-column');
    
    kanbanColumns.forEach(column => {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onStart: function(evt) {
                evt.item.classList.add('dragging');
            },
            onEnd: function(evt) {
                evt.item.classList.remove('dragging');
                
                // Update task status when moved to different column
                const taskId = evt.item.dataset.taskId;
                const newStatus = evt.to.dataset.status;
                
                if (taskId && newStatus) {
                    updateTaskStatus(taskId, newStatus);
                }
            }
        });
    });
}

// Update task status via HTMX
function updateTaskStatus(taskId, status) {
    htmx.ajax('POST', `/api/tasks/${taskId}/status`, {
        values: { status: status },
        target: `[data-task-id="${taskId}"]`,
        swap: 'outerHTML'
    }).then(() => {
        showNotification('Task status updated successfully', 'success');
    }).catch(error => {
        console.error('Error updating task status:', error);
        showNotification('Error updating task status', 'error');
    });
}

// HTMX search functionality is handled by hx-get attributes in HTML

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus on first invalid field
                const firstInvalidField = form.querySelector(':invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            }
            
            form.classList.add('was-validated');
        });
    });
}

// Initialize tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize modals
function initializeModals() {
    // Auto-focus on modal inputs when shown
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
}

// Notification system
function initializeNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
}

// Show notification
function showNotification(message, type = 'info', duration = 3000) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

// Delete task function
function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        fetch(`/tasks/${taskId}/delete`, {
            method: 'POST'
        })
        .then(response => {
            if (response.ok) {
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskCard) {
                    taskCard.remove();
                }
                showNotification('Task deleted successfully', 'success');
            } else {
                showNotification('Error deleting task', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting task:', error);
            showNotification('Error deleting task', 'error');
        });
    }
}

// Delete project function
function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project? This will also delete all tasks in this project.')) {
        fetch(`/projects/${projectId}/delete`, {
            method: 'POST'
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '/projects';
            } else {
                showNotification('Error deleting project', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting project:', error);
            showNotification('Error deleting project', 'error');
        });
    }
}

// Auto-refresh dashboard stats using HTMX
function refreshDashboardStats() {
    if (window.location.pathname === '/dashboard' || window.location.pathname === '/') {
        htmx.ajax('GET', '/api/dashboard/stats', {
            target: '#stats-cards',
            swap: 'innerHTML'
        }).catch(error => {
            console.error('Error refreshing stats:', error);
        });
    }
}

// Update stats cards
function updateStatsCards(stats) {
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(card => {
        const type = card.dataset.type;
        const numberElement = card.querySelector('.stats-number');
        
        if (numberElement && stats[type]) {
            numberElement.textContent = stats[type];
        }
    });
}

// Auto-refresh is handled by HTMX hx-trigger="every 30s"

// HTMX event handlers
document.addEventListener('htmx:beforeRequest', function(event) {
    // Show loading indicator
    const target = event.target;
    target.classList.add('htmx-request');
});

document.addEventListener('htmx:afterRequest', function(event) {
    // Hide loading indicator
    const target = event.target;
    target.classList.remove('htmx-request');
    
    // Reinitialize components for newly loaded content
    initializeTooltips();
    initializeKanban();
});

document.addEventListener('htmx:responseError', function(event) {
    showNotification('An error occurred. Please try again.', 'error');
});

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    // Ctrl/Cmd + K for search
    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Ctrl/Cmd + N for new task
    if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
        event.preventDefault();
        window.location.href = '/tasks/create';
    }
});

// Service Worker for offline functionality (if needed)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(registration => {
            console.log('Service Worker registered successfully');
        })
        .catch(error => {
            console.log('Service Worker registration failed');
        });
}

// Export functions for global use
window.TaskManager = {
    deleteTask,
    deleteProject,
    updateTaskStatus,
    showNotification,
    refreshDashboardStats
};
