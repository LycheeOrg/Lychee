# Lychee Installation Controllers Documentation

This document explains the controllers responsible for Lychee's web-based installation process. These controllers guide users through a step-by-step installation workflow, ensuring all requirements are met and the system is properly configured.

> **Note**: It is very likely that you won't ever need to modify these controllers unless you want revamp the complete installation process. Lychee aims to be installed mostly via docker, or having the key generation and database migration done via CLI. The screen that will be visited by the user is only for the initial setup of the admin user, which is required to access the application.

## Overview

The installation controllers implement a multi-step installation wizard that validates system requirements, configures the environment, sets up the database, and creates the initial admin user. Each controller represents a specific step in the installation process.

### Installation Flow

The installation process follows this sequence:
1. **Welcome** - Introduction and start
2. **Requirements** - System requirements validation  
3. **Permissions** - File/directory permissions check
4. **Environment** - `.env` file configuration
5. **Migration** - Database setup and key generation
6. **Admin Setup** - Create initial admin user

## Controllers Overview

### 1. WelcomeController

**Purpose**: Displays the welcome screen and installation introduction.

**Route**: `GET /install/` (named route: `install-welcome`)

**Key Features**:
- Simple welcome view with no validation
- Sets installation step to 0
- Entry point for the installation process

**Usage**:
```php
public function view(): View
{
    return view('install.welcome', [
        'title' => 'Lychee-installer',
        'step' => 0,
    ]);
}
```

### 2. RequirementsController

**Purpose**: Validates system requirements for running Lychee.

**Route**: `GET /install/req` (named route: `install-req`)

**Dependencies**:
- `RequirementsChecker` - Validates PHP version and extensions
- `DefaultConfig` - Provides minimum requirements configuration

**Key Features**:
- PHP version validation against minimum requirements
- Extension and module availability checks
- Displays detailed error information for failed requirements

**Validation Process**:
1. Check PHP version compatibility
2. Validate required PHP extensions
3. Check optional but recommended extensions
4. Return detailed status for each requirement

**Usage**:
```php
public function view(): View
{
    $php_support_info = $this->requirements->checkPHPVersion(
        $this->config->get_core()['minPhpVersion']
    );
    $reqs = $this->requirements->check(
        $this->config->get_requirements()
    );

    return view('install.requirements', [
        'phpSupportInfo' => $php_support_info,
        'requirements' => $reqs['requirements'],
        'errors' => $reqs['errors'] ?? null,
    ]);
}
```

### 3. PermissionsController

**Purpose**: Validates file and directory permissions required by Lychee.

**Route**: `GET /install/perm` (named route: `install-perm`)

**Dependencies**:
- `PermissionsChecker` - Validates directory write permissions
- `DefaultConfig` - Provides required permissions configuration

**Key Features**:
- Directory write permission validation
- Windows compatibility detection
- Detailed permission status reporting
- Error highlighting for insufficient permissions

**Checked Permissions**:
- Storage directories (photos, cache, logs)
- Configuration file access
- Public directory permissions
- Upload directory permissions

**Usage**:
```php
public function view(): View
{
    $perms = $this->permissions->check(
        $this->config->get_permissions()
    );

    return view('install.permissions', [
        'permissions' => $perms['permissions'],
        'errors' => $perms['errors'],
        'windows' => $this->permissions->is_win(),
    ]);
}
```

### 4. EnvController

**Purpose**: Manages `.env` file configuration for database and application settings.

**Route**: `GET|POST /install/env` (named route: `install-env`)

**Key Features**:
- Displays `.env` configuration editor
- Handles both viewing and saving of environment configuration
- Automatically loads `.env.example` as template if `.env` doesn't exist
- Validates and saves environment configuration

**Configuration Process**:
1. **GET Request**: Display current `.env` or `.env.example` content
2. **POST Request**: Save provided environment configuration
3. File locking for safe writes
4. Error handling for I/O operations

**Usage**:
```php
public function view(Request $request): View
{
    if ($request->has('envConfig')) {
        // Save new configuration
        $env = str_replace("\r", '', $request->get('envConfig'));
        file_put_contents(base_path('.env'), $env, LOCK_EX);
        $exists = true;
    } elseif (file_exists(base_path('.env'))) {
        // Load existing configuration
        $env = file_get_contents(base_path('.env'));
        $exists = true;
    } else {
        // Load template configuration
        $env = file_get_contents(base_path('.env.example'));
        $exists = false;
    }

    return view('install.env', [
        'env' => $env,
        'exists' => $exists,
    ]);
}
```


### 5. MigrationController

**Purpose**: Executes database migrations and generates application key.

**Route**: `GET /install/migrate` (named route: `install-migrate`)

**Key Features**:
- Uses Laravel Pipeline pattern for sequential operations
- Database migration execution
- Application key generation
- View cache clearing
- Comprehensive error reporting

**Pipeline Operations**:
1. **ArtisanViewClear** - Clear compiled views
2. **ArtisanMigrate** - Run database migrations  
3. **QueryExceptionChecker** - Validate database operations
4. **Spacer** - Add output formatting
5. **ArtisanKeyGenerate** - Generate application key

**Usage**:
```php
public function view(): View
{
    $output = [];
    $has_errors = false;
    
    try {
        $output = app(Pipeline::class)
            ->send($output)
            ->through([
                ArtisanViewClear::class,
                ArtisanMigrate::class,
                QueryExceptionChecker::class,
                Spacer::class,
                ArtisanKeyGenerate::class,
                Spacer::class,
            ])
            ->thenReturn();
    } catch (InstallationFailedException) {
        $has_errors = true;
    }

    return view('install.migrate', [
        'lines' => $output,
        'errors' => $has_errors,
    ]);
}
```

**Error Handling**:
- Catches and reports installation failures
- Provides detailed output from each pipeline step
- Displays user-friendly error messages

### 6. SetUpAdminController

**Purpose**: Creates the initial administrator user account.

**Routes**: 
- `GET /install/admin` - Display admin creation form
- `POST /install/admin` - Process admin creation

**Dependencies**:
- `SetUpAdminRequest` - Validates admin user input
- `User` model - Creates admin user record
- `Configs` model - Sets owner configuration

**Key Features**:
- Admin user creation with full privileges
- Password hashing and security
- Owner configuration setup
- Form validation and error handling

**Admin User Privileges**:
- `may_upload` - Can upload photos
- `may_edit_own_settings` - Can modify personal settings
- `may_administrate` - Has administrative access

**Usage**:
```php
// GET - Display form
public function init(): View
{
    return view('install.setup-admin', ['step' => 5]);
}

// POST - Create admin user
public function create(SetUpAdminRequest $request): View
{
    try {
        $user = new User();
        $user->may_upload = true;
        $user->may_edit_own_settings = true;
        $user->may_administrate = true;
        $user->username = $request->username();
        $user->password = Hash::make($request->password());
        $user->save();

        Configs::set('owner_id', $user->id);
        
        return view('install.setup-success');
    } catch (\Throwable $e) {
        return view('install.setup-admin', [
            'error' => $e->getMessage(),
        ]);
    }
}
```

## Middleware and Security

### Installation Middleware

The installation controllers use specific middleware to control access:

- **`installation:incomplete`** - Prevents access to main application during installation
- **`installation:complete`** - Only allows admin setup after successful installation
- **`admin_user:unset`** - Ensures admin setup only runs when no admin exists

### Request Validation

**SetUpAdminRequest** validates admin creation:
- `username` - Must pass `UsernameRule` validation
- `password` - Must pass `PasswordRule` validation and be confirmed
- Custom error bag for installation-specific errors

## Installation State Management

The installation process maintains state through:

1. **File Existence Checks** - Validates `.env` and other critical files
2. **Database State** - Checks for completed migrations
3. **Admin User Existence** - Determines if initial setup is complete
4. **Middleware Guards** - Prevents access to inappropriate installation steps

## Error Handling Patterns

### Common Error Types

1. **File Permission Errors** - Insufficient write access to directories
2. **Database Connection Errors** - Invalid database configuration
3. **PHP Extension Errors** - Missing required PHP modules
4. **Environment Errors** - Invalid `.env` configuration

### Error Display Strategy

- **Requirements/Permissions** - Detailed technical information for system administrators
- **Environment** - Configuration-specific guidance
- **Migration** - Step-by-step operation feedback
- **Admin Setup** - User-friendly validation messages

## Best Practices

### For Developers

1. **Error Handling** - Always provide meaningful error messages
2. **State Validation** - Check installation state before proceeding
3. **Security** - Validate all user input and sanitize outputs
4. **Logging** - Use pipeline pattern for trackable operations

### For System Administrators

1. **Prerequisites** - Ensure all requirements are met before starting
2. **Backup** - Have database backups before running migrations
3. **Permissions** - Set appropriate file/directory permissions
4. **Configuration** - Verify database credentials before proceeding

This installation system provides a robust, user-friendly way to deploy Lychee while ensuring all requirements are met and the system is properly configured for production use.

---

*Last updated: August 14, 2025*
