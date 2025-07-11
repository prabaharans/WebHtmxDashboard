# Task Manager Application

A comprehensive web-based task management system built with PHP, Node.js, and HTMX, featuring real-time updates, database management, and Kanban-style boards.

## Features

### Core Functionality
- **Task Management**: Create, edit, delete, and update tasks with status tracking
- **Project Organization**: Group tasks by projects with full CRUD operations
- **Kanban Board**: Interactive drag-and-drop interface for task status updates
- **Real-time Search**: Dynamic search functionality with HTMX integration
- **Dashboard**: Overview with statistics and recent tasks

### Database Support
- **SQLite**: Primary database for development with automatic schema creation
- **PostgreSQL**: Production-ready database support with environment configuration
- **Database Administration**: Web-based admin interface for database management
- **Migration System**: Version-controlled database schema management
- **Connection Testing**: Built-in database connectivity testing

### User Interface
- **Responsive Design**: Mobile-first Bootstrap-based interface
- **Real-time Updates**: HTMX-powered interactions without page refreshes
- **Interactive Components**: Drag-and-drop, search, and form validation
- **Modern Styling**: Clean, professional design with Font Awesome icons

## Technology Stack

### Backend
- **Node.js/Express**: Server-side runtime and web framework
- **SQLite**: Development database with automatic setup
- **PostgreSQL**: Production database support
- **PHP**: Database administration and migration tools

### Frontend
- **HTMX**: Dynamic server-side interactions
- **Bootstrap 5**: Responsive UI framework
- **SortableJS**: Drag-and-drop functionality
- **Font Awesome**: Icon library

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd task-manager
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm start
   # or
   node server.js
   ```

4. **Access the application**
   - Main application: http://localhost:5000
   - Database status: http://localhost:5000/database/status.php
   - Database admin: http://localhost:5000/database/admin.php

## Database Configuration

### SQLite (Development)
The application automatically creates and configures a SQLite database with sample data on first run.

### PostgreSQL (Production)
Set the following environment variables:
```bash
export DB_TYPE=postgresql
export PGHOST=your-host
export PGPORT=5432
export PGDATABASE=task_manager
export PGUSER=your-username
export PGPASSWORD=your-password
# or use DATABASE_URL
export DATABASE_URL=postgresql://username:password@host:port/database
```

## API Endpoints

### Tasks
- `GET /api/tasks/search?q=query` - Search tasks
- `POST /api/tasks/:id/status` - Update task status
- `GET /api/dashboard/stats` - Get dashboard statistics

### Database
- `GET /api/database/test` - Test database connection
- `GET /api/database/stats` - Get database statistics

## Database Schema

### Projects Table
- `id`: Primary key (auto-increment)
- `name`: Project name (required)
- `description`: Project description
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### Tasks Table
- `id`: Primary key (auto-increment)
- `title`: Task title (required)
- `description`: Task description
- `status`: Task status (todo, in_progress, done)
- `priority`: Task priority (low, medium, high)
- `assigned_to`: Assigned user email
- `project_id`: Foreign key to projects table
- `due_date`: Task due date
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

## File Structure

```
task-manager/
├── assets/
│   ├── css/
│   │   └── styles.css       # Custom styles
│   └── js/
│       └── app.js           # Client-side JavaScript
├── config/
│   ├── database.php         # Database configuration
│   └── routes.php           # PHP routing
├── controllers/
│   ├── DashboardController.php
│   ├── ProjectController.php
│   └── TaskController.php
├── database/
│   ├── admin.php            # Database admin interface
│   ├── status.php           # Database status page
│   ├── migrations.php       # Migration system
│   └── task_manager.db      # SQLite database file
├── models/
│   ├── Database.php         # Database connection class
│   ├── Project.php          # Project model
│   └── Task.php             # Task model
├── views/
│   ├── components/          # Reusable components
│   ├── projects/            # Project views
│   ├── tasks/               # Task views
│   ├── dashboard.php        # Dashboard view
│   └── layout.php           # Base layout
├── server.js                # Node.js server
├── package.json             # Node.js dependencies
└── README.md               # This file
```

## Usage

### Creating Tasks
1. Navigate to the Tasks section
2. Click "Create Task" 
3. Fill in task details including title, description, priority, and project
4. Submit the form

### Managing Projects
1. Go to the Projects section
2. Create new projects or edit existing ones
3. View project details and associated tasks

### Kanban Board
1. Access the Kanban view from the Tasks section
2. Drag and drop tasks between columns (Todo, In Progress, Done)
3. Tasks update in real-time

### Database Management
1. Visit the Database section in the navigation
2. View connection status and statistics
3. Access the admin panel for advanced operations
4. Run SQL queries directly through the web interface

## Development

### Adding New Features
1. Create appropriate model classes in `models/`
2. Add controllers in `controllers/`
3. Create views in `views/`
4. Update routing in `server.js`

### Database Migrations
1. Add new migration methods in `database/migrations.php`
2. Run migrations via the admin interface or command line
3. Test with both SQLite and PostgreSQL

### Frontend Development
1. Modify HTMX attributes for dynamic interactions
2. Add custom CSS in `assets/css/styles.css`
3. Update JavaScript in `assets/js/app.js`

## Deployment

### Development
- Use SQLite database (automatic setup)
- Run with `node server.js`
- Access on `http://localhost:5000`

### Production
- Configure PostgreSQL environment variables
- Set up proper web server (nginx/apache)
- Use process manager (pm2, systemd)
- Enable SSL/TLS certificates

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues and questions:
1. Check the database status page for connection issues
2. Review the console logs for errors
3. Use the database admin interface for data issues
4. Check the GitHub issues page for known problems