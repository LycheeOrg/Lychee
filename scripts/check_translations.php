#!/usr/bin/env php
<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Translation Key Validation Script
 * Checks that all translation keys exist across all language files.
 */
function getKeys(array $array, string $prefix = ''): array
{
	$keys = [];
	foreach ($array as $key => $value) {
		$full_key = $prefix !== '' ? $prefix . '.' . $key : $key;
		if (is_array($value)) {
			$keys = array_merge($keys, getKeys($value, $full_key));
		} else {
			$keys[] = $full_key;
		}
	}

	return $keys;
}

function checkFile(string $filename, array $languages, string $lang_dir): array
{
	$en_file = $lang_dir . '/en/' . $filename;
	if (!file_exists($en_file)) {
		return ["Error: English file not found: $en_file"];
	}

	$en_keys = getKeys(require $en_file);
	sort($en_keys);

	$report = [];
	$report[] = "\n" . str_repeat('=', 80);
	$report[] = "Checking: $filename";
	$report[] = str_repeat('=', 80);
	$report[] = 'Total keys in English: ' . count($en_keys);

	$all_missing = [];

	foreach ($languages as $lang) {
		if ($lang === 'en') {
			continue;
		}

		$lang_file = $lang_dir . '/' . $lang . '/' . $filename;
		if (!file_exists($lang_file)) {
			$report[] = "\n[$lang] ❌ FILE MISSING: $lang_file";
			$all_missing[$lang] = $en_keys;
			continue;
		}

		$lang_keys = getKeys(require $lang_file);
		$missing = array_diff($en_keys, $lang_keys);
		$extra = array_diff($lang_keys, $en_keys);

		if (count($missing) > 0 || count($extra) > 0) {
			$report[] = "\n[$lang] Issues found:";
			if (count($missing) > 0) {
				$report[] = '  ❌ Missing ' . count($missing) . ' keys:';
				foreach ($missing as $key) {
					$report[] = "     - $key";
				}
				$all_missing[$lang] = $missing;
			}
			if (count($extra) > 0) {
				$report[] = '  ⚠️  Extra ' . count($extra) . ' keys (not in English):';
				foreach ($extra as $key) {
					$report[] = "     + $key";
				}
			}
		} else {
			$report[] = "[$lang] ✅ All keys present (" . count($lang_keys) . ' keys)';
		}
	}

	if (count($all_missing) === 0) {
		$report[] = "\n🎉 All languages complete for $filename!";
	} else {
		$report[] = "\n⚠️  " . count($all_missing) . ' languages have missing keys';
	}

	return $report;
}

// Main execution
$lang_dir = __DIR__ . '/../lang';
$languages = ['ar', 'cz', 'de', 'el', 'en', 'es', 'fa', 'fr', 'hu', 'it', 'ja', 'nl', 'no', 'pl', 'pt', 'ru', 'sk', 'sv', 'vi', 'zh_CN', 'zh_TW'];

$files_to_check = ['gallery.php', 'profile.php'];

echo "Translation Validation Report\n";
echo 'Generated: ' . date('Y-m-d H:i:s') . "\n";
echo 'Languages checked: ' . implode(', ', array_filter($languages, fn ($l) => $l !== 'en')) . "\n";

$all_reports = [];
foreach ($files_to_check as $file) {
	$all_reports = array_merge($all_reports, checkFile($file, $languages, $lang_dir));
}

foreach ($all_reports as $line) {
	echo $line . "\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "Validation complete\n";
