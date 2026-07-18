<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

declare(strict_types=1);

namespace App\Services\Zip;

use App\Exceptions\Internal\ZipBombDetectedException;
use RuntimeException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fread;
use function Safe\fwrite;
use function Safe\mkdir;
use function Safe\realpath;
use function Safe\unlink;

/**
 * SafeZipExtractor.
 *
 * Defends against zip-bomb (decompression) and zip-slip (path traversal)
 * attacks when handling untrusted .zip uploads.
 *
 * Strategy (defense in depth):
 *   1. Pre-flight inspection of the central directory (cheap, catches
 *      classic bombs that honestly declare their huge uncompressed size).
 *   2. Streamed extraction that counts REAL bytes written and aborts the
 *      instant a limit is exceeded (catches bombs that lie in their header).
 *   3. Path sanitisation so entries can't escape the destination dir.
 */
final class SafeZipExtractor
{
	public function __construct(
		// Max combined uncompressed size of the whole archive, in bytes.
		private int $max_total_size,
		// Max uncompressed size of any single entry, in bytes.
		private int $max_file_size,
		// Max number of entries (guards against "many tiny files" DoS).
		private int $max_entries,
		// Max overall compression ratio (uncompressed / compressed).
		private int $max_ratio,
		// Read chunk size while streaming.
		private int $chunk_size = 8192,
	) {
	}

	/**
	 * Cheap pre-flight check based on the archive's declared sizes.
	 * Returns true if the archive *claims* to be within limits.
	 *
	 * NOTE: A malicious archive can under-report sizes, so this is a
	 * fast first filter only — extraction still enforces real limits.
	 */
	public function inspect(string $zip_path): bool
	{
		$zip = new \ZipArchive();
		if ($zip->open($zip_path, \ZipArchive::RDONLY) !== true) {
			throw new \RuntimeException('Unable to open archive.');
		}

		try {
			if ($zip->numFiles > $this->max_entries) {
				return false;
			}

			$total_uncompressed = 0;
			$total_compressed = 0;

			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				if ($stat === false) {
					return false;
				}

				if ($stat['size'] > $this->max_file_size) {
					return false;
				}

				$total_uncompressed += $stat['size'];
				$total_compressed += $stat['comp_size'];

				if ($total_uncompressed > $this->max_total_size) {
					return false;
				}
			}

			if ($total_compressed > 0 &&
				($total_uncompressed / $total_compressed) > $this->max_ratio) {
				return false;
			}

			return true;
		} finally {
			$zip->close();
		}
	}

	/**
	 * Safely extract the archive to $dest_dir.
	 * Throws RuntimeException the moment anything looks like an attack.
	 */
	public function extract(string $zip_path, string $dest_dir): void
	{
		if (!$this->inspect($zip_path)) {
			throw new ZipBombDetectedException('Archive rejected by pre-flight inspection.');
		}

		if (!is_dir($dest_dir)) {
			mkdir($dest_dir, 0755, true);
		}
		$base_real = realpath($dest_dir);

		$zip = new \ZipArchive();
		if ($zip->open($zip_path, \ZipArchive::RDONLY) !== true) {
			throw new \RuntimeException('Unable to open archive.');
		}

		$written_total = 0;

		try {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				$name = $stat['name'];

				// Skip directory entries after resolving their safe path.
				$target = $this->resolveSafeTarget($base_real, $name);
				if ($target === null) {
					throw new \RuntimeException("Blocked path traversal: {$name}");
				}

				if (str_ends_with($name, '/')) {
					if (!is_dir($target)) {
						mkdir($target, 0755, true);
					}
					continue;
				}

				$dir = dirname($target);
				if (!is_dir($dir)) {
					mkdir($dir, 0755, true);
				}

				$in = $zip->getStream($name);
				if ($in === false) {
					throw new \RuntimeException("Cannot read entry: {$name}");
				}

				try {
					$out = fopen($target, 'wb');
				} catch (\Throwable $e) {
					fclose($in);
					throw new \RuntimeException("Cannot write file: {$name}", previous: $e);
				}

				$file_written = 0;

				try {
					while (!feof($in)) {
						$chunk = fread($in, $this->chunk_size);

						$len = strlen($chunk);
						$file_written += $len;
						$written_total += $len;

						// The real, non-negotiable limits — measured from
						// bytes actually produced, not from the header.
						if ($file_written > $this->max_file_size) {
							throw new ZipBombDetectedException("Entry exceeds per-file limit: {$name}");
						}
						if ($written_total > $this->max_total_size) {
							throw new ZipBombDetectedException('Archive exceeds total size limit.');
						}

						fwrite($out, $chunk);
					}
				} catch (\Throwable $e) {
					fclose($in);
					fclose($out);
					if (is_file($target)) {
						unlink($target);   // don't leave a partial bomb on disk
					}
					throw $e;
				}

				fclose($in);
				fclose($out);
			}
		} finally {
			$zip->close();
		}
	}

	/**
	 * Resolve an entry name to an absolute path guaranteed to live inside
	 * $base_real, or null if the entry tries to escape (zip slip).
	 */
	private function resolveSafeTarget(string $base_real, string $entry_name): ?string
	{
		if ($entry_name === '' || str_contains($entry_name, "\0")) {
			return null;
		}

		// Normalise separators and strip any leading slashes / drive letters.
		$entry = str_replace('\\', '/', $entry_name);
		$entry = ltrim($entry, '/');

		$parts = [];
		foreach (explode('/', $entry) as $segment) {
			if ($segment === '' || $segment === '.') {
				continue;
			}
			if ($segment === '..') {
				if (count($parts) === 0) {
					return null; // trying to climb above base
				}
				array_pop($parts);
				continue;
			}
			$parts[] = $segment;
		}

		if (count($parts) === 0) {
			return null;
		}

		$target = $base_real . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);

		// Final belt-and-braces prefix check.
		if (!str_starts_with($target, $base_real . DIRECTORY_SEPARATOR)) {
			return null;
		}

		return $target;
	}
}
