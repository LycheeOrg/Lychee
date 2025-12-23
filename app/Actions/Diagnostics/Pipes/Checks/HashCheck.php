<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\DTO\DiagnosticDTO;
use function Safe\hash_update_file;

/**
 * Calculate the hash of Lychee installation to validate integrity.
 */
class HashCheck implements DiagnosticPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(DiagnosticDTO &$data, \Closure $next): DiagnosticDTO
	{
		$paths_to_scan = [
			app_path(),
			base_path('bootstrap'),
			config_path(),
			base_path('database/migrations'),
			lang_path(),
			resource_path(),
			public_path('build'),
			base_path('routes'),
			base_path('version.md'),
			base_path('composer.json'),
			base_path('composer.lock'),
		];

		$files = $this->collectFiles($paths_to_scan);
		$files_hash = $this->computeHash($files, 'xxh3');

		$vendor_files = $this->collectFiles([base_path('vendor')]);
		$vendor_hash = $this->computeHash($vendor_files, 'xxh3');

		$data->data[] = DiagnosticData::info('Hash: ' . $files_hash . '—' . $vendor_hash, self::class, [
			(string) count($files) . ' files and ' . (string) count($vendor_files) . ' vendor files',
		]);

		return $next($data);
	}

	/**
	 * Collect all files from the provided paths (files or directories).
	 *
	 * @param string[] $paths
	 *
	 * @return string[] absolute, sorted file paths
	 */
	private function collectFiles(array $paths): array
	{
		$file_list = [];

		foreach ($paths as $path) {
			if (!is_string($path)) {
				continue;
			}

			if (is_file($path)) {
				$file_list[] = $path;
				continue;
			}

			if (is_dir($path)) {
				$directory_iterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
				$iterator = new \RecursiveIteratorIterator($directory_iterator);
				foreach ($iterator as $file_info) {
					if ($file_info instanceof \SplFileInfo && $file_info->isFile()) {
						$file_list[] = $file_info->getPathname();
					}
				}
			}
		}

		// Sort deterministically by path to ensure stable hash
		sort($file_list, SORT_STRING);

		return $file_list;
	}

	/**
	 * Compute a combined hash of the provided files using incremental hashing.
	 * The file path is included alongside file contents to prevent collisions
	 * where different files contain identical data.
	 *
	 * @param string[] $files
	 * @param string   $algo
	 *
	 * @return string hex-encoded hash
	 */
	private function computeHash(array $files, string $algo): string
	{
		$selected_algo = in_array($algo, hash_algos(), true) ? $algo : 'sha256';
		$ctx = hash_init($selected_algo);

		foreach ($files as $file_path) {
			// Add the path itself to the stream for extra safety and ordering
			$rel = $this->normalizePath($file_path);
			hash_update($ctx, 'PATH::' . $rel . "\n");
			if (is_readable($file_path) && is_file($file_path)) {
				// Update with file content; ignore errors silently (e.g., permission changes)
				try {
					hash_update_file($ctx, $file_path);
				} catch (\Exception $e) {
					hash_update($ctx, "UNREADABLE\n");
				}
			} else {
				hash_update($ctx, "UNREADABLE\n");
			}
		}

		return hash_final($ctx);
	}

	/**
	 * Normalize absolute path to a base-relative, forward‑slash path for stable hashing.
	 */
	private function normalizePath(string $abs_path): string
	{
		$base = rtrim(base_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if (strncmp($abs_path, $base, strlen($base)) === 0) {
			$rel = substr($abs_path, strlen($base));
		} else {
			$rel = $abs_path;
		}
		// Normalize separators for cross‑platform stability
		$rel = str_replace('\\', '/', $rel);

		return ltrim($rel, '/');
	}
}
