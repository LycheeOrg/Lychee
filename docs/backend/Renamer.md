# Renamer Module Documentation

## Overview

The Renamer module in Lychee provides functionality to create, manage, and apply rules for renaming files during the import process. It allows users to define patterns and their replacements to transform filenames based on custom rules. This module is particularly useful for standardizing filenames across a collection or replacing camera-generated prefixes with more meaningful names.

## Architecture

### Core Components

1. **Renamer Class**: Located in `App\Metadata\Renamer`, this is the main class that handles the application of renamer rules to strings.
2. **RenamerRule Model**: Represents a single renaming rule in the database.
3. **RenamerModeType Enum**: Defines the available replacement modes (FIRST, ALL, REGEX).
4. **RenamerController**: Handles API requests for managing renaming rules.

### Database Schema

The Renamer module uses a `renamer_rules` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| owner_id | bigint | Foreign key to the user who owns this rule |
| name | string | Name of the rule |
| description | string | Optional description of what the rule does |
| needle | string | The string to find (pattern to match) |
| replacement | string | The replacement text |
| mode | enum | Mode of operation (FIRST, ALL, REGEX) |
| order | int | Processing order (lower numbers processed first) |
| is_enabled | boolean | Whether the rule is active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## API Endpoints

The Renamer module exposes API endpoints for managing rules and testing filename transformations. Refer to the `api_v2.php` routes file for the specific implementation details of these endpoints.

## Implementation Details

### Mode Types

The Renamer module supports three modes of operation (defined in the `RenamerModeType` enum):

1. **First occurrence** (`FIRST`): Replaces only the first occurrence of the pattern.
2. **All occurrences** (`ALL`): Replaces all occurrences of the pattern.
3. **Regular expression** (`REGEX`): Uses regular expressions for pattern matching and replacement.

### Processing Order

Rules are processed in order from lowest to highest `order` value, meaning rules with lower order numbers have higher priority. Only enabled rules are applied.

### Global Configuration

The Renamer functionality can be controlled through several configuration settings:

1. `renamer_enabled`: Global switch to enable/disable renaming functionality.
2. `renamer_enforced`: If enabled, only the system owner's rules are used, overriding user-specific rules.
3. `renamer_enforced_before`: Apply system owner's rules before user-specific rules.
4. `renamer_enforced_after`: Apply system owner's rules after user-specific rules.

### Integration Points

1. **Import Process**: The Renamer module is integrated with Lychee's import process to rename files upon upload or during server-side imports.
2. **Permission System**: The renamer functionality checks if a user has supporter status through the `Verify` class.

## Usage in Code

### Applying Renamer Rules

```php
// Create a Renamer instance for a specific user
$renamer = new Renamer($user_id);

// Apply renamer rules to a single filename
$newFilename = $renamer->handle('IMG_1234.jpg');

// Apply renamer rules to multiple filenames
$newFilenames = $renamer->handleMany(['IMG_1234.jpg', 'DSC_5678.jpg']);
```

### Saving a New Rule

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id(); // Current user's ID
$rule->name = 'Replace IMG_';
$rule->description = 'Replaces IMG_ with Photo_';
$rule->needle = 'IMG_';
$rule->replacement = 'Photo_';
$rule->mode = RenamerModeType::FIRST;
$rule->order = 1;
$rule->is_enabled = true;
$rule->save();
```

## Future Enhancements

1. **Rule Groups**: Allow grouping rules for different import types or albums.
2. **Import Photo Integration**: Allow associating specific data to the rule: e.g. Exif info.
3. **User Interface**: Allow executing renamer rules directly from the UI, on an album or selected set of photos.

---

*Last updated: August 21, 2025*
