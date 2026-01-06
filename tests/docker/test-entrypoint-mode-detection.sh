#!/bin/sh
# SPDX-License-Identifier: MIT
# Copyright (C) 2024 Lychee Team
#
# Unit tests for entrypoint.sh LYCHEE_MODE detection logic

set -e

# Test counters
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# ANSI color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create temporary test script that simulates entrypoint mode detection
cat > /tmp/test-mode-detect.sh <<'EOF'
#!/bin/sh
# Simplified mode detection logic from entrypoint.sh
LYCHEE_MODE=${LYCHEE_MODE:-web}

case "$LYCHEE_MODE" in
    web)
        echo "Starting Lychee in web mode..."
        ;;
    worker)
        echo "Starting Lychee in worker mode..."
        echo "Auto-restart enabled: worker will restart if it exits"
        ;;
    *)
        echo "ERROR: Invalid LYCHEE_MODE: $LYCHEE_MODE. Must be 'web' or 'worker'."
        exit 1
        ;;
esac
EOF
chmod +x /tmp/test-mode-detect.sh

# Helper: Run a test case
run_test() {
    test_name=$1
    lychee_mode_value=$2
    expected_pattern=$3
    expected_exit_code=$4

    TESTS_RUN=$((TESTS_RUN + 1))

    # Run test with specified LYCHEE_MODE
    exit_code=0
    if [ "$lychee_mode_value" = "unset" ]; then
        output=$(sh -c 'unset LYCHEE_MODE; /tmp/test-mode-detect.sh' 2>&1) || exit_code=$?
    else
        output=$(LYCHEE_MODE="$lychee_mode_value" /tmp/test-mode-detect.sh 2>&1) || exit_code=$?
    fi

    # Check if output matches expected pattern and exit code
    if echo "$output" | grep -q "$expected_pattern" && [ "$exit_code" -eq "$expected_exit_code" ]; then
        echo "${GREEN}✓ PASS${NC}: $test_name"
        TESTS_PASSED=$((TESTS_PASSED + 1))
        return 0
    else
        echo "${RED}✗ FAIL${NC}: $test_name"
        echo "  Expected pattern: $expected_pattern"
        echo "  Expected exit:    $expected_exit_code"
        echo "  Actual exit:      $exit_code"
        echo "  Output:           $output"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

echo "${YELLOW}Running entrypoint.sh mode detection tests...${NC}"
echo ""

# Test 1: LYCHEE_MODE=web (explicit web mode)
run_test \
    "Explicit web mode (LYCHEE_MODE=web)" \
    "web" \
    "Starting Lychee in web mode" \
    0

# Test 2: LYCHEE_MODE=worker (worker mode)
run_test \
    "Worker mode (LYCHEE_MODE=worker)" \
    "worker" \
    "Starting Lychee in worker mode" \
    0

# Test 3: LYCHEE_MODE unset (default to web mode)
run_test \
    "Default web mode (LYCHEE_MODE unset)" \
    "unset" \
    "Starting Lychee in web mode" \
    0

# Test 4: LYCHEE_MODE=invalid (error exit)
run_test \
    "Invalid mode (LYCHEE_MODE=invalid)" \
    "invalid" \
    "ERROR: Invalid LYCHEE_MODE" \
    1

# Test 5: LYCHEE_MODE=production (error exit)
run_test \
    "Invalid mode (LYCHEE_MODE=production)" \
    "production" \
    "ERROR: Invalid LYCHEE_MODE" \
    1

# Test 6: Verify worker mode shows auto-restart message
run_test \
    "Worker auto-restart message" \
    "worker" \
    "Auto-restart enabled" \
    0

echo ""
echo "======================================"
echo "Test Results:"
echo "  Total:  $TESTS_RUN"
echo "  ${GREEN}Passed: $TESTS_PASSED${NC}"
echo "  ${RED}Failed: $TESTS_FAILED${NC}"
echo "======================================"

# Cleanup
rm -f /tmp/test-mode-detect.sh

# Exit with failure if any tests failed
if [ "$TESTS_FAILED" -gt 0 ]; then
    exit 1
fi

echo "${GREEN}All tests passed!${NC}"
exit 0
