#!/bin/bash
# Codex Commit Review Script
# Generates a conventional commit message based on staged changes

set -e

echo "=== Codex Commit Review ==="
echo ""

# Check for staged changes
if ! git diff --cached --quiet; then
    echo "Staged files:"
    git diff --cached --name-status
    echo ""

    # Analyze changes
    docs_changed=$(git diff --cached --name-only | grep -c "^docs/" || true)
    src_changed=$(git diff --cached --name-only | grep -cE "^(app|resources|routes|config|database)/" || true)
    tests_changed=$(git diff --cached --name-only | grep -c "^tests/" || true)

    echo "Change summary:"
    echo "  - Documentation files: $docs_changed"
    echo "  - Source files: $src_changed"
    echo "  - Test files: $tests_changed"
    echo ""

    # Suggest commit type
    if [ "$docs_changed" -gt 0 ] && [ "$src_changed" -eq 0 ]; then
        suggested_type="docs"
    elif [ "$tests_changed" -gt 0 ] && [ "$src_changed" -eq 0 ]; then
        suggested_type="test"
    elif [ "$src_changed" -gt 0 ]; then
        suggested_type="feat"
    else
        suggested_type="chore"
    fi

    echo "Suggested commit type: $suggested_type"
    echo ""

    # Check if docs and code changed together
    if [ "$docs_changed" -gt 0 ] && [ "$src_changed" -gt 0 ]; then
        echo "NOTE: Both docs and code changed. Include 'Spec impact:' line in commit body."
    fi

    echo ""
    echo "=== Suggested Commit Message Format ==="
    echo ""
    echo "$suggested_type(<scope>): <brief description>"
    echo ""
    echo "<optional body with details>"
    if [ "$docs_changed" -gt 0 ] && [ "$src_changed" -gt 0 ]; then
        echo ""
        echo "Spec impact: <describe how documentation changes relate to code>"
    fi
    echo ""
else
    echo "No staged changes found. Stage files with 'git add' first."
    exit 1
fi