# Stackvel Framework

**Minimal MVC. Maximum Control.**

A lightweight, secure PHP MVC framework inspired by Laravel, designed to provide maximum developer control with minimal overhead.

## 🚀 Features

- **Lightweight & Fast**: Minimal overhead, optimized for performance
- **Secure by Default**: Built with security best practices
- **Eloquent-style ORM**: PDO-based database operations with familiar syntax
- **Blade Templating**: Powerful template engine with layouts and components
- **Email Support**: PHPMailer integration for HTML emails
- **CLI Commands**: Console tools for development and maintenance
- **Cronjob Support**: Scheduled task execution
- **PSR-4 Autoloading**: Modern PHP standards compliance
- **Environment Configuration**: Flexible configuration management
- **Session Management**: Secure session handling with CSRF protection

## 📁 Project Structure

```
Stackvel/
├── app/
│   ├── Controllers/     # Application controllers
│   └── Models/         # Eloquent-style models
├── core/               # Framework core components
│   ├── Application.php # Main application class
│   ├── Router.php      # URL routing system
│   ├── Database.php    # Database connectivity
│   ├── Model.php       # Base model class
│   ├── View.php        # Blade templating engine
│   ├── Mailer.php      # Email functionality
│   ├── Session.php     # Session management
│   └── Config.php      # Configuration management
├── routes/
│   └── web.php         # Web routes definition
├── resources/
│   └── views/          # Blade template files
├── public/
│   ├── index.php       # Application entry point
│   └── .htaccess       # Apache configuration
├── console/
│   └── Kernel.php      # CLI and cronjob support
├── config/             # Configuration files
├── composer.json       # Composer dependencies
├── console.php         # Console entry point
└── README.md           # This file
```

## 🛠 Installation

### Prerequisites

- PHP 8.0 or higher
- Composer
- MySQL/MariaDB (or other PDO-supported database)
- Apache/Nginx web server

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/pawan1793/stackvel.git
   cd stackvel
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp env.example .env
   # Edit .env with your database and mail settings
   ```

4. **Set up database**
   ```bash
   # Create your database
   # Update .env with database credentials
   ```

5. **Start development server**
   ```bash
   php console.php serve
   ```

6. **Visit your application**
   ```
   http://localhost:8000
   ```

## ⚙️ Configuration

### Environment Variables

Copy `env.example` to `.env` and configure:

```env
# Application
APP_NAME=Stackvel
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stackvel
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=Stackvel
```

## 🛣 Routing

Define routes in `routes/web.php`:

```php
// Basic routes
$router->get('/', 'HomeController@index');
$router->post('/users', 'UserController@store');

// Route with parameters
$router->get('/users/{id}', 'UserController@show');

// Route groups
$router->group(['prefix' => 'admin', 'middleware' => ['AuthMiddleware']], function ($router) {
    $router->get('/', 'AdminController@dashboard');
});

// Closure-based routes
$router->get('/test', function () {
    return 'Hello from Stackvel!';
});
```

## 🎮 Controllers

Create controllers in `app/Controllers/`:

```php
<?php

namespace App\Controllers;

class UserController extends Controller
{
    public function index(): string
    {
        $users = User::all();
        return $this->view('users.index', ['users' => $users]);
    }

    public function store(): void
    {
        $data = $this->input();
        
        // Validate input
        $errors = $this->validate($data, [
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            $this->redirect('/users/create');
            return;
        }

        User::create($data);
        $this->flash('success', 'User created successfully.');
        $this->redirect('/users');
    }
}
```

## 🗄 Models

Create models in `app/Models/`:

```php
<?php

namespace App\Models;

use Stackvel\Model;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name', 'email', 'password'
    ];
    
    protected array $hidden = [
        'password'
    ];

    // Eloquent-style methods
    public static function findByEmail(string $email): ?static
    {
        return static::whereFirst('email', $email);
    }
}
```

### Multiple Database Connections

Stackvel supports multiple database connections. Specify a connection in your model:

```php
<?php

namespace App\Models;

use Stackvel\Model;

class otherdbUser extends Model
{
    protected string $table = 'users';
    
    // Specify which database connection to use
    protected string $connection = 'mysql_otherdb';
    
    protected array $fillable = [
        'name', 'email', 'password'
    ];
}
```

Configure connections in your `.env` file:

```env
# Default connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stackvel
DB_USERNAME=root
DB_PASSWORD=

# Additional connection
DB_OTHER_DATABASE=otherdb
```

Use different connections:

```php
// Uses default connection
$user = \App\Models\User::create(['name' => 'John', 'email' => 'john@example.com']);

// Uses mysql_otherdb connection
$otherdbUser = \App\Models\OtherdbUser::create(['name' => 'Jane', 'email' => 'jane@otherdb.com']);

// Direct database access
$app = \Stackvel\Application::getInstance();
$defaultDb = $app->database->connection('mysql');
$otherdbDb = $app->database->connection('mysql_otherdb');
```

See [Multiple Database Connections Documentation](docs/multiple-database-connections.md) for more details.

## 🎨 Views (Blade Templates)

Create views in `resources/views/`:

```php
@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <h1>Users</h1>
    
    @foreach($users as $user)
        <div class="user-card">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
        </div>
    @endforeach
@endsection
```

### Blade Directives

- `@if`, `@elseif`, `@else`, `@endif`
- `@foreach`, `@endforeach`
- `@extends`, `@section`, `@yield`
- `@include`
- `@csrf` (CSRF protection)
- `@method` (HTTP method override)
- `@old` (old input)
- `@error` (validation errors)

## 📧 Email

Send emails using the Mailer component:

```php
// In a controller
$this->sendEmail(
    'user@example.com',
    'Welcome!',
    '<h1>Welcome to our application!</h1>'
);

// Using view templates
$this->sendEmailView(
    'user@example.com',
    'Welcome!',
    'emails.welcome',
    ['user' => $user]
);
```

## 🖥 Console Commands

Use the console for development and maintenance:

```bash
# Show available commands
php console.php help

# Start development server
php console.php serve

# Run database migrations
php console.php migrate

# Seed database
php console.php seed

# Clear cache
php console.php clear-cache

# Optimize for production
php console.php optimize

# Run scheduled tasks
php console.php schedule daily

# Create new controller
php console.php make:controller UserController

# Create new model
php console.php make:model User

# Create new migration
php console.php make:migration create_users_table
```

## ⏰ Scheduled Tasks

Configure scheduled tasks in `console/Kernel.php`:

```php
protected function registerScheduledTasks(): void
{
    $this->scheduledTasks = [
        'daily' => [
            'cleanup:logs' => 'Clean up old log files',
            'backup:database' => 'Create database backup'
        ],
        'hourly' => [
            'check:system' => 'Check system health'
        ]
    ];
}
```

Run with cron:
```bash
# Daily tasks
0 0 * * * cd /path/to/stackvel && php console.php schedule daily

# Hourly tasks
0 * * * * cd /path/to/stackvel && php console.php schedule hourly
```

## 🔒 Security Features

- **CSRF Protection**: Built-in CSRF token generation and validation
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Output escaping in templates
- **Session Security**: Secure session configuration
- **Input Validation**: Comprehensive validation system
- **Security Headers**: Automatic security headers

## 🧪 Testing

Run tests with PHPUnit:

```bash
composer test
```

## 📚 API Documentation

### Database Operations

```php
// Find all records
$users = User::all();

// Find by ID
$user = User::find(1);

// Find by column
$user = User::whereFirst('email', 'user@example.com');

// Create record
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update record
$user->name = 'Jane Doe';
$user->save();

// Delete record
$user->delete();
```

### Session Management

```php
// Set session value
$this->session->set('user_id', 123);

// Get session value
$userId = $this->session->get('user_id');

// Flash messages
$this->session->flash('success', 'Operation completed!');

// Get flash message
$message = $this->session->getFlash('success');
```

### Validation

```php
$errors = $this->validate($data, [
    'name' => 'required|string|min:2',
    'email' => 'required|email',
    'password' => 'required|string|min:6'
]);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


## 🙏 Acknowledgments

- Inspired by Laravel's elegant syntax and structure
- Built with modern PHP 8.0+ features
- Uses PHPMailer for email functionality
- Bootstrap for responsive UI components

---

**Stackvel Framework** - Where minimal meets powerful. 🚀 
