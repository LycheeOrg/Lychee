<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Renamer Rules
	|--------------------------------------------------------------------------
	*/

	// Page title
	'title' => 'Renamer Rules',

	// Modal titles
	'create_rule' => 'Create Renamer Rule',
	'edit_rule' => 'Edit Renamer Rule',

	// Form fields
	'rule_name' => 'Rule Name',
	'description' => 'Description',
	'pattern' => 'Pattern',
	'replacement' => 'Replacement',
	'mode' => 'Mode',
	'order' => 'Order',
	'enabled' => 'Enabled',
	'photo_rule' => 'Rule applied to Photos',
	'album_rule' => 'Rule applied to Albums',

	// Form placeholders and help text
	'description_placeholder' => 'Optional description of what this rule does',
	'pattern_help' => 'Pattern to match (e.g., IMG_, DSC_)',
	'replacement_help' => 'Replacement text (e.g., Photo_, Camera_)',
	'order_help' => 'Lower numbers are processed first (1 = highest priority)',
	'enabled_help' => '(Only enabled rules will be applied during renaming)',

	// Mode options
	'mode_first' => 'First occurrence',
	'mode_all' => 'All occurrences',
	'mode_regex' => 'Regular expression',
	'mode_trim' => 'Trim whitespace',
	'mode_strtolower' => 'lowercase',
	'mode_strtoupper' => 'UPPERCASE',
	'mode_ucwords' => 'Uppercase Each Word',
	'mode_ucfirst' => 'Uppercase first letter',

	'mode_first_description' => 'Replace only the first occurrence',
	'mode_all_description' => 'Replace all occurrences',
	'mode_regex_description' => 'Use regex pattern matching',
	'mode_trim_description' => 'Trim whitespace',
	'mode_strtolower_description' => 'Convert string to lowercases',
	'mode_strtoupper_description' => 'Convert string to UPPERCASE',
	'mode_ucwords_description' => 'Capitalize each words',
	'mode_ucfirst_description' => 'Capitalize only the first letter',

	'regex_help' => 'Use regular expressions to match patterns. For example, to replace <code>IMG_1234.jpeg</code> with <code>1234_JPG.jpeg</code>, you can use <code>/IMG_(\d+)/</code> as needle and <code>$1_JPG</code> as replacement. You can find more explanations and examples in the following links.',

	// Buttons
	'cancel' => 'Cancel',
	'create' => 'Create',
	'update' => 'Update',
	'create_first_rule' => 'Create your first rule',

	// Validation messages
	'rule_name_required' => 'Rule name is required',
	'pattern_required' => 'Pattern is required',
	'replacement_required' => 'Replacement is required',
	'mode_required' => 'Mode is required',
	'order_positive' => 'Order must be a positive number',

	// Success messages
	'rule_created' => 'Renamer rule created successfully',
	'rule_updated' => 'Renamer rule updated successfully',
	'rule_deleted' => 'Renamer rule deleted successfully',

	// Error messages
	'failed_to_create' => 'Failed to create renamer rule',
	'failed_to_update' => 'Failed to update renamer rule',
	'failed_to_delete' => 'Failed to delete renamer rule',
	'failed_to_load' => 'Failed to load renamer rules',

	// List view
	'rules_count' => ':count rules',
	'no_rules' => 'No renamer rules found',
	'loading' => 'Loading renamer rules...',
	'pattern_label' => 'Pattern',
	'replace_with_label' => 'Replace with',
	'photo' => 'Photo',
	'album' => 'Album',
	'photo' => 'Photo',
	'album' => 'Album',

	// Delete confirmation
	'confirm_delete_header' => 'Confirm Deletion',
	'confirm_delete_message' => 'Are you sure you want to delete the rule ":rule"?',
	'delete' => 'Delete',

	// Status messages
	'success' => 'Success',
	'error' => 'Error',

	// Placeholders
	'select_mode' => 'Select renaming mode',
	'execution_order' => 'Execution order',

	// Test functionality
	'test_input_placeholder' => 'Enter a filename to test your renamer rules (e.g., IMG_1234.jpg)',
	'test_original' => 'Original',
	'test_result' => 'Result',
	'test_failed' => 'Failed to test renamer rules',
];
