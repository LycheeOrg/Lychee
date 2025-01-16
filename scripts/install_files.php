<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

include_once __DIR__ . '/../vendor/autoload.php';

use function Safe\copy;
use function Safe\touch;

$NO_COLOR = "\033[0m";
$GREEN = "\033[38;5;010m";
$YELLOW = "\033[38;5;011m";
$ORANGE = "\033[38;5;214m";

echo "\n{$YELLOW}creating file for CSS personalization$NO_COLOR" . PHP_EOL;
touch('public/dist/user.css');

echo "\n{$YELLOW}creating file for JS personalization$NO_COLOR" . PHP_EOL;
touch('public/dist/custom.js');

echo "\n{$YELLOW}creating default SQLite database$NO_COLOR" . PHP_EOL;
touch('database/database.sqlite');

if (is_dir('.git')) {
	echo "\n{$YELLOW}setting up hooks for git pull and git commits$NO_COLOR" . PHP_EOL;
	copy('scripts/pre-commit', '.git/hooks/pre-commit');
	copy('scripts/post-merge', '.git/hooks/post-merge');
}

echo "\n{$ORANGE}To disable the call of composer and migration on pull add$NO_COLOR" . PHP_EOL;
echo "{$ORANGE}a file named '.NO_AUTO_COMPOSER_MIGRATE' in this directory.$NO_COLOR" . PHP_EOL;