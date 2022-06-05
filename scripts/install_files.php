<?php

include_once __DIR__ . '/../vendor/autoload.php';

$NO_COLOR = "\033[0m";
$GREEN = "\033[38;5;010m";
$YELLOW = "\033[38;5;011m";
$ORANGE = "\033[38;5;214m";

echo "\n${YELLOW}creating file for CSS personalization$NO_COLOR\n";
\Safe\touch('public/dist/user.css');

echo "\n${YELLOW}creating default SQLite database$NO_COLOR\n";
\Safe\touch('database/database.sqlite');

echo "\n${YELLOW}setting up hooks for git pull and git commits$NO_COLOR\n";
\Safe\copy('scripts/pre-commit', '.git/hooks/pre-commit');
\Safe\copy('scripts/post-merge', '.git/hooks/post-merge');

echo "\n${ORANGE}To disable the call of composer and migration on pull add$NO_COLOR\n";
echo "${ORANGE}a file named '.NO_AUTO_COMPOSER_MIGRATE' in this directory.$NO_COLOR\n";
