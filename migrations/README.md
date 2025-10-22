# Database Migrations

This folder contains database migration scripts for the SU Mentoring platform.

## Running Migrations

### Option 1: Using MySQL Command Line

```bash
# Navigate to project root
cd C:\Apache24\htdocs\SU_mentoring

# Run the migration
mysql -u root -p mentoring_website < migrations/add_mentor_availability_table.sql
```

### Option 2: Using phpMyAdmin

1. Open phpMyAdmin in your browser
2. Select the `mentoring_website` database
3. Click on "SQL" tab
4. Copy and paste the contents of the migration file
5. Click "Go" to execute

### Option 3: Using PHP Script

Create a file `run_migration.php` in the project root:

```php
<?php
require_once 'includes/db.php';

$migrationFile = 'migrations/add_mentor_availability_table.sql';
$sql = file_get_contents($migrationFile);

try {
    global $pdo;
    $pdo->exec($sql);
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
```

Then run: `php run_migration.php`

## Available Migrations

### add_mentor_availability_table.sql
- **Purpose**: Creates the `mentor_availability` table for storing mentor time slots
- **Required for**: Availability management feature (pages/availability.php)
- **Date**: October 2025
- **Status**: Ready to deploy

## Migration Best Practices

1. **Backup First**: Always backup your database before running migrations
2. **Test Locally**: Test migrations on development environment first
3. **Check Dependencies**: Ensure required tables exist before running
4. **Review Changes**: Read the migration file before executing
5. **Version Control**: Keep track of which migrations have been run

## Troubleshooting

### Error: Table already exists
The migration uses `CREATE TABLE IF NOT EXISTS`, so it's safe to run multiple times.

### Error: Foreign key constraint fails
Ensure the `users` table exists and has mentors with `role = 'mentor'`.

### Error: Access denied
Check your database credentials in `includes/.env.php`.

## Need Help?

See the main README.md or SETUP.md for more information.
