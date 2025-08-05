# Stackvel Framework - Routing Setup Guide

This guide explains how to configure routing for both subdirectory installations and development server scenarios.

## Access Methods

### 1. Direct Access to Public Directory
When accessing your application through a web server with the project in a subdirectory:

**URL Format:** `http://localhost/stackvel/public/`

**Configuration:**
- Set `APP_URL=http://localhost/stackvel/public` in your `.env` file
- The router automatically detects the subdirectory path
- All routes work relative to the subdirectory

### 2. Development Server
When using the built-in PHP development server:

**Command:** `php console.php serve`
**URL Format:** `http://127.0.0.1:8000`

**Configuration:**
- Set `APP_URL=http://127.0.0.1:8000` in your `.env` file
- The router automatically detects this is a development server
- All routes work from the root path

## Environment Configuration

Create a `.env` file in your project root with the following settings:

```env
# For subdirectory access
APP_URL=http://localhost/stackvel/public

# For development server
APP_URL=http://127.0.0.1:8000

# Other settings
APP_NAME=Stackvel
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=UTC
```

## Testing Routes

### Test Route
Visit `/routing-test` to verify routing is working correctly. This route returns debugging information about the current request.

### Available Routes
- `/` - Home page
- `/about` - About page  
- `/contact` - Contact page
- `/test` - Simple test response
- `/json` - JSON response
- `/routing-test` - Routing debug information

## URL Generation

Use the helper functions to generate URLs that work with both access methods:

```php
// Generate URLs
url('/about')           // Works with both methods
url('contact')          // Automatically adds leading slash
asset('css/style.css')  // For static assets

// In controllers
$this->redirect('/about');
```

## Troubleshooting

### Routes Not Working
1. Check your `.env` file has the correct `APP_URL`
2. Ensure Apache mod_rewrite is enabled
3. Verify `.htaccess` file is present in the `public/` directory
4. Check file permissions on the `public/` directory

### 404 Errors
1. Verify the route is defined in `routes/web.php`
2. Check that the controller and method exist
3. Ensure the route pattern matches the URL

### Subdirectory Issues
1. Make sure `APP_URL` includes the full subdirectory path
2. Check that the web server is configured to serve from the correct directory
3. Verify the `.htaccess` RewriteBase setting if needed

## Apache Configuration

For subdirectory installations, you may need to uncomment and modify the RewriteBase in `public/.htaccess`:

```apache
# Uncomment and modify for subdirectory installations
RewriteBase /stackvel/public/
```

## Development Workflow

1. **Development:** Use `php console.php serve` for local development
2. **Testing:** Test both access methods to ensure compatibility
3. **Production:** Configure web server to point to the `public/` directory
4. **Deployment:** Update `APP_URL` to match your production domain 