#!/usr/bin/env php
<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

include_once __DIR__ . '/../vendor/autoload.php';

use function Safe\date;
use Safe\Exceptions\FilesystemException;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\scandir;

/**
 * Template for migration.
 */
$template = "<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '%s']);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', 'version')->update(['value' => '%s']);
	}
};
";

/**
 * We get the current version number.
 *
 * @return array<int,int>
 */
function get_version(): array
{
	try {
		$str_CurrentVersion = file_get_contents('version.md');
	} catch (FilesystemException) {
		throw new Exception("unable to find current version number in version.md\n");
	}
	$arr_CurrentVersion = array_map('intval', explode('.', $str_CurrentVersion));
	if (count($arr_CurrentVersion) !== 3) {
		throw new Exception('invalid version number');
	}

	return $arr_CurrentVersion;
}

/**
 * Given the current version and the update array return the new version number.
 *
 * @param array<int,int> $curr_version current version number
 * @param string         $kind         'minor' or 'major'
 *
 * @return array<int,int>
 */
function new_version(array $curr_version, string $kind): array
{
	$new_version = $curr_version;
	if ($kind === 'major') {
		$new_version[1]++;
		$new_version[2] = 0;
	} else {
		$new_version[2]++;
	}

	if ($curr_version[1] === 100 || $curr_version[2] === 100) {
		throw new Exception('Maybe it is time for a big change?');
	}

	return $new_version;
}

/**
 * encode $version into a string of 6 digits.
 *
 * @param array<int,int> $version
 *
 * @return string
 */
function str_version(array $version): string
{
	return sprintf('%02d%02d%02d', $version[0], $version[1], $version[2]);
}

/**
 * Check if migration with same name already exists.
 */
function does_migration_exists(string $version): void
{
	$name_candidate = 'bump_version' . $version;
	$migrations = array_slice(scandir('database/migrations'), 2);
	foreach ($migrations as $migration) {
		if (is_dir('database/migrations/' . $migration)) {
			continue;
		}

		// given 2020_04_22_155712_bump_version040002.php we retrieve bump_version040002
		$name = explode('_', $migration, 5);
		$name = substr($name[4], 0, -4);
		if ($name === $name_candidate) {
			throw new Exception('Migration ' . $name_candidate . ' already exsists!');
		}
	}
}

$kind = $argv[1] ?? 'minor';

try {
	$cv = get_version();
	$nv = new_version($cv, $kind);

	$str_cv = str_version($cv);
	$str_nv = str_version($nv);

	does_migration_exists($str_nv);

	$fileName = sprintf('database/migrations/%s_bump_version%s.php', date('Y_m_d_His'), $str_nv);
	$fileContent = sprintf($template, $str_nv, $str_cv);

	file_put_contents($fileName, $fileContent);
	echo "Migration generated!\n";

	file_put_contents('version.md', implode('.', $nv));
	echo "version.md updated!\n";

	exit;
} catch (Exception $e) {
	exit("\e[0;31m" . $e->getMessage() . "\e[0m\n");
}
