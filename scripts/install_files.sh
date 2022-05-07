#!/bin/sh
NO_COLOR="\033[0m"
GREEN="\033[38;5;010m"
YELLOW="\033[38;5;011m"
ORANGE="\033[38;5;214m"

printf "\n${YELLOW}creating file for css personalization${NO_COLOR}\n"
echo "touch public/dist/user.css"
touch public/dist/user.css

printf "\n${YELLOW}creating default sqlite database${NO_COLOR}\n"
echo "touch database/database.sqlite"
touch database/database.sqlite

printf "\n${YELLOW}setting up hooks for git pull and git commits${NO_COLOR}\n"
echo "cp scripts/pre-commit .git/hooks/"
cp scripts/pre-commit .git/hooks/
echo "cp scripts/post-merge .git/hooks/"
cp scripts/post-merge .git/hooks/

printf "\n${ORANGE}To disable the call of composer and migration on pull add${NO_COLOR}\n"
printf "${ORANGE}a file named '.NO_AUTO_COMPOSER_MIGRATE' in this directory.${NO_COLOR}\n"
