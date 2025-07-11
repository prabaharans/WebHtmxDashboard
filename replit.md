# Task Manager Application

## Overview

This is a comprehensive web-based task management application built with PHP and HTMX. The application features a modern interface with real-time updates, Kanban-style boards, and full CRUD operations for tasks and projects. It uses SQLite for data storage and Node.js as the development server with full HTMX integration for dynamic user interactions.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Client-side Framework**: Vanilla JavaScript with modern ES6+ features
- **UI Framework**: Bootstrap-based responsive design with custom CSS
- **Drag & Drop**: SortableJS library for Kanban board interactions
- **HTMX Integration**: For seamless server-side interactions without full page reloads
- **Module Structure**: Modular JavaScript architecture with separate initialization functions

### Backend Architecture
- **Server Technology**: Node.js/Express with SQLite database
- **Template Engine**: Server-side rendering with dynamic content updates
- **API Design**: RESTful endpoints handling task and project operations
- **Database**: Dual database support (SQLite for development, PostgreSQL for production)

### Database Architecture
- **Primary Database**: SQLite (development) with PostgreSQL support (production)
- **ORM/Query Builder**: Raw SQL with prepared statements
- **Schema Management**: Database migrations system with version control
- **Data Integrity**: Foreign key constraints, check constraints, and indexes
- **Administration**: Web-based database admin interface with query execution

### Styling Strategy
- **CSS Architecture**: Custom CSS with CSS custom properties (variables)
- **Design System**: Consistent color scheme and component styling
- **Responsive Design**: Mobile-first approach with Bootstrap integration

## Key Components

### Task Management System
- **Kanban Board**: Interactive drag-and-drop interface for task status updates
- **Task Cards**: Individual task representation with hover effects and transitions
- **Status Tracking**: Real-time status updates when tasks are moved between columns

### Project Management
- **Project Cards**: Dedicated components for project display and management
- **Project Organization**: Hierarchical structure for organizing tasks within projects

### User Interface Components
- **Navigation**: Bootstrap navbar with custom branding
- **Search Functionality**: Client-side search implementation
- **Form Validation**: Built-in form validation system
- **Tooltips**: Enhanced user experience with contextual help
- **Modals**: Popup interfaces for detailed interactions
- **Notifications**: User feedback system for actions and updates

### Interactive Features
- **Drag & Drop**: Full Kanban board functionality with visual feedback
- **Real-time Updates**: HTMX-powered updates without page refreshes
- **Animation System**: Smooth transitions and hover effects
- **Responsive Interactions**: Touch-friendly mobile interface

## Data Flow

### Task Status Updates
1. User drags task card to new column
2. JavaScript captures the move event
3. Extract task ID and new status from DOM elements
4. Send HTMX request to update task status
5. Server processes update and returns response
6. UI updates reflect the new status

### Search Implementation
1. User enters search query
2. Client-side JavaScript filters visible tasks/projects
3. Real-time results display without server round-trip

### Form Interactions
1. User fills out forms for task/project creation
2. Client-side validation provides immediate feedback
3. Valid forms submit via HTMX for server processing
4. Server response updates the UI with new content

## External Dependencies

### JavaScript Libraries
- **SortableJS**: Drag-and-drop functionality for Kanban boards
- **HTMX**: Server communication and dynamic content updates
- **Bootstrap**: UI framework for responsive design and components

### CSS Framework
- **Bootstrap**: Base styling and component library
- **Custom CSS**: Application-specific styling and theming

### Database Components
- **SQLite**: Primary database for development with automatic schema creation
- **PostgreSQL**: Production database support with environment-based configuration
- **Database Administration**: Web-based admin interface for database management
- **Migration System**: Version-controlled database schema management

## Deployment Strategy

### Static Assets
- CSS and JavaScript files served from `/assets/` directory
- Optimized for browser caching with version control

### Architecture Considerations
- **Progressive Enhancement**: Core functionality works without JavaScript
- **Performance**: Minimal JavaScript footprint with efficient DOM manipulation
- **Accessibility**: Semantic HTML structure with proper ARIA attributes
- **Mobile Optimization**: Touch-friendly interfaces and responsive design

### Development Workflow
- **Modular Code**: Separate initialization functions for different features
- **Event-Driven**: DOM-ready initialization with proper event handling
- **Error Handling**: Graceful degradation for network issues and JavaScript errors

The application follows modern web development best practices with a focus on user experience, maintainability, and performance. The architecture supports both immediate interactivity and server-side data persistence through a well-structured client-server communication pattern.