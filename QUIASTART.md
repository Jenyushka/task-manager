# Task Manager - Quick Start Guide

## Installation Steps

### 1. Prerequisites

Ensure you have installed:
- PHP 8.2 or higher (`php -v`)
- Composer (`composer --version`)
- MySQL 8.0+ or MariaDB 10.11+

### 2. Install Project

```bash
# Navigate to project directory
cd task-manager

# Install dependencies
composer install
```

### 3. Configure Database

Edit `.env` file:

```env
# For MySQL
DATABASE_URL="mysql://root:password@127.0.0.1:3306/task_manager?serverVersion=8.0.32&charset=utf8mb4"

# For MariaDB
DATABASE_URL="mysql://root:password@127.0.0.1:3306/task_manager?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

Replace:
- `root` with your database username
- `password` with your database password
- `127.0.0.1:3306` with your database host and port
- `task_manager` with your preferred database name

### 4. Create Database

```bash
# Create the database
php bin/console doctrine:database:create

# Run migrations to create tables
php bin/console doctrine:migrations:migrate
```

If you get an error about migrations, you can create the schema directly:
```bash
php bin/console doctrine:schema:create
```

### 5. Start the Server

Option A - Using Symfony CLI (recommended):
```bash
symfony server:start
```

Option B - Using PHP built-in server:
```bash
php -S localhost:8000 -t public/
```

### 6. Access the Application

Open your browser and navigate to:
- **Main app**: http://localhost:8000/task/
- **Create task**: http://localhost:8000/task/new

## Usage Examples

### Web Interface

1. **View all tasks**
    - Go to http://localhost:8000/task/

2. **Create a task with notes**
    - Click "New Task" → "Create Task with Notes"
    - Fill in: Title, Description, Notes
    - Click "Create Task"

3. **Create a task with priority**
    - Click "New Task" → "Create Task with Priority"
    - Fill in: Title, Description, Priority (low/medium/high/critical)
    - Click "Create Task"

4. **Complete a task**
    - Click "Complete" button on any open task

5. **View task details**
    - Click on any task title

### CLI Interface

Create a task from command line:

```bash
php bin/console app:task:create
```

Follow the interactive prompts:
1. Select task type (1 or 2)
2. Enter title
3. Enter description
4. Enter type-specific fields (notes or priority)

## Testing

Run all tests:
```bash
php bin/phpunit
```

Run specific test suites:
```bash
# Unit tests only
php bin/phpunit tests/Unit

# Functional tests only
php bin/phpunit tests/Functional
```

## Troubleshooting

### Error: "No such file or directory" for database

**Solution**: Make sure MySQL/MariaDB is running:
```bash
# Check if MySQL is running
sudo service mysql status

# Start MySQL if not running
sudo service mysql start
```

### Error: "SQLSTATE[HY000] [1045] Access denied"

**Solution**: Check your database credentials in `.env` file

### Error: "Class not found" or autoload issues

**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
```

### Error: "Cache directory not writable"

**Solution**: Clear and set proper permissions:
```bash
php bin/console cache:clear
chmod -R 777 var/
```

## Next Steps

After installation, you can:

1. **Read the full README**: See `README.md` for complete documentation
2. **Study the architecture**: See `ARCHITECTURE.md` for design decisions
3. **Add new task types**: Follow the guide in README.md
4. **Run tests**: Ensure everything works correctly

## Quick Reference

### Common Commands

```bash
# Clear cache
php bin/console cache:clear

# List all routes
php bin/console debug:router

# List all commands
php bin/console list

# Check Doctrine mapping
php bin/console doctrine:mapping:info

# Validate schema
php bin/console doctrine:schema:validate

# Create migration
php bin/console doctrine:migrations:diff

# Run migrations
php bin/console doctrine:migrations:migrate
```

### Routes

- `GET  /task/` - List all tasks
- `GET  /task/new` - Select task type
- `GET  /task/new/with-notes` - Create task with notes form
- `POST /task/new/with-notes` - Submit task with notes
- `GET  /task/new/with-priority` - Create task with priority form
- `POST /task/new/with-priority` - Submit task with priority
- `GET  /task/{id}` - View task details
- `POST /task/{id}/complete` - Mark task as completed
- `POST /task/{id}/reopen` - Reopen completed task
- `POST /task/{id}` - Delete task

## Support

For questions or issues, please refer to:
- README.md for detailed documentation
- ARCHITECTURE.md for design decisions and extensibility guide
