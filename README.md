# Task Manager Application

A Symfony 7.2 application for managing tasks with different types and priorities.

## Features

- ✅ Create, list, and complete tasks
- 📝 Two task types:
  - **Task with Notes**: Tasks with an additional notes field
  - **Task with Priority**: Tasks with priority levels (low, medium, high, critical)
- 🖥️ Web interface for task management
- 💻 CLI command for creating tasks
- 🧪 PHPUnit tests included
- 🗄️ Uses Doctrine ORM with MySQL/MariaDB

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or MariaDB 10.11+
- Required PHP extensions: ctype, iconv, xml, mbstring, curl, zip, mysql, intl, sqlite3

## Installation

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Database

Edit the `.env` file and update the `DATABASE_URL`:

```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/task_manager?serverVersion=8.0.32&charset=utf8mb4"
```

For MariaDB:
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/task_manager?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

### 3. Create Database and Schema

```bash
# Create the database
php bin/console doctrine:database:create

# Run migrations to create tables
php bin/console doctrine:migrations:migrate
```

If migrations don't exist yet, you can generate the schema directly:
```bash
php bin/console doctrine:schema:create
```

### 4. Start the Development Server

```bash
symfony server:start
```

Or using PHP's built-in server:
```bash
php -S localhost:8000 -t public/
```

Visit: http://localhost:8000/task/

## Usage

### Web Interface

1. **View all tasks**: Navigate to `/task/`
2. **Create a new task**: 
   - Click "New Task"
   - Select task type (with notes or with priority)
   - Fill in the form and submit
3. **View task details**: Click on a task title
4. **Complete a task**: Click "Complete" button on any open task
5. **Reopen a task**: Click "Reopen" button on any completed task

### CLI (Command Line Interface)

Create a task via command line:

```bash
php bin/console app:task:create
```

The command will interactively prompt you for:
- Task type (with notes or with priority)
- Title
- Description
- Type-specific fields (notes or priority)

## Architecture & Design

### Entity Hierarchy

The application uses **Single Table Inheritance** for task types:

```
Task (abstract base class)
├── TaskWithNotes (adds 'notes' field)
└── TaskWithPriority (adds 'priority' field)
```

**Why Single Table Inheritance?**
- All task types share common fields (title, description, status, createdAt)
- Simple to query all tasks together
- Easy to add new task types in the future
- No complex joins required

**Database Schema:**
```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(255),           -- Discriminator column
    title VARCHAR(255),
    description TEXT,
    created_at DATETIME,
    status VARCHAR(20),
    notes TEXT,                  -- For TaskWithNotes
    priority VARCHAR(20)         -- For TaskWithPriority
);
```

### Adding New Task Types

To add a new task type:

1. **Create Entity Class**:
```php
<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskWithDeadline extends Task
{
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $deadline = null;

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): static
    {
        $this->deadline = $deadline;
        return $this;
    }

    public function getTypeName(): string
    {
        return 'Task with Deadline';
    }
}
```

2. **Update Discriminator Map** in `Task.php`:
```php
#[ORM\DiscriminatorMap([
    'task_with_notes' => TaskWithNotes::class,
    'task_with_priority' => TaskWithPriority::class,
    'task_with_deadline' => TaskWithDeadline::class  // Add this
])]
```

3. **Create Form Type**:
```php
<?php
namespace App\Form;

use App\Entity\TaskWithDeadline;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class TaskWithDeadlineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('deadline', DateTimeType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskWithDeadline::class,
        ]);
    }
}
```

4. **Add Controller Actions** in `TaskController.php`:
```php
#[Route('/new/with-deadline', name: 'task_new_with_deadline')]
public function newWithDeadline(Request $request, EntityManagerInterface $em): Response
{
    $task = new TaskWithDeadline();
    $form = $this->createForm(TaskWithDeadlineType::class, $task);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($task);
        $em->flush();
        return $this->redirectToRoute('task_index');
    }

    return $this->render('task/new.html.twig', [
        'form' => $form,
        'task_type' => 'Task with Deadline'
    ]);
}
```

5. **Update Templates**: Add the new type to `select_type.html.twig`

6. **Run Migration**:
```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Key Design Decisions

1. **Separation of Concerns**:
   - Entities handle data structure and business logic
   - Repositories handle data access
   - Controllers handle HTTP requests/responses
   - Forms handle validation and data binding
   - Commands handle CLI interactions

2. **Validation**:
   - Uses Symfony Validator constraints
   - Validation rules defined in entity attributes
   - Automatic validation in forms

3. **Type Safety**:
   - PHP 8.2 type hints throughout
   - Nullable types where appropriate
   - Return type declarations

4. **Extensibility**:
   - Abstract base class allows easy addition of new task types
   - Repository pattern for data access
   - Form types for reusable forms

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

### Test Coverage

- **Unit Tests**:
  - `TaskWithNotesTest`: Tests entity behavior, status changes, creation
  - `TaskWithPriorityTest`: Tests priority levels, entity behavior
  - `TaskRepositoryTest`: Tests custom repository methods

- **Functional Tests**:
  - `TaskControllerTest`: Tests web interface, form submission, validation

## Project Structure

```
task-manager/
├── bin/
│   └── console              # CLI entry point
├── config/
│   ├── packages/           # Bundle configurations
│   ├── routes.yaml         # Route configuration
│   └── services.yaml       # Service container
├── migrations/             # Database migrations
├── public/
│   └── index.php          # Web entry point
├── src/
│   ├── Command/           # CLI commands
│   │   └── TaskCreateCommand.php
│   ├── Controller/        # Web controllers
│   │   └── TaskController.php
│   ├── Entity/            # Doctrine entities
│   │   ├── Task.php
│   │   ├── TaskWithNotes.php
│   │   └── TaskWithPriority.php
│   ├── Form/              # Form types
│   │   ├── TaskWithNotesType.php
│   │   └── TaskWithPriorityType.php
│   ├── Repository/        # Data access
│   │   └── TaskRepository.php
│   └── Kernel.php         # Application kernel
├── templates/             # Twig templates
│   ├── base.html.twig
│   └── task/
│       ├── index.html.twig
│       ├── new.html.twig
│       ├── select_type.html.twig
│       └── show.html.twig
├── tests/                 # PHPUnit tests
│   ├── Functional/
│   └── Unit/
├── .env                   # Environment configuration
├── composer.json          # Dependencies
└── phpunit.xml.dist      # PHPUnit configuration
```

## Technologies Used

- **Symfony 7.2**: PHP framework
- **Doctrine ORM 3.0**: Database abstraction and ORM
- **Twig**: Template engine
- **PHPUnit 10.5**: Testing framework
- **Symfony Form**: Form handling and validation
- **Symfony Console**: CLI commands

## Future Enhancements

- [ ] Add task editing functionality
- [ ] Add filtering and sorting options
- [ ] Add pagination for large task lists
- [ ] Add user authentication and task ownership
- [ ] Add task categories/tags
- [ ] Add due dates and reminders
- [ ] Add file attachments
- [ ] REST API for external integrations

## License

This project is created for educational/demonstration purposes.
