<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

declare(strict_types=1);

namespace App\Services\Zip;

use RuntimeException;
use Throwable;
use ZipArchive;

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
		private int $maxTotalSize,
		// Max uncompressed size of any single entry, in bytes.
		private int $maxFileSize,
		// Max number of entries (guards against "many tiny files" DoS).
		private int $maxEntries,
		// Max overall compression ratio (uncompressed / compressed).
		private int $maxRatio,
		// Read chunk size while streaming.
		private int $chunkSize = 8192,
	) {
	}

	/**
	 * Cheap pre-flight check based on the archive's declared sizes.
	 * Returns true if the archive *claims* to be within limits.
	 *
	 * NOTE: A malicious archive can under-report sizes, so this is a
	 * fast first filter only — extraction still enforces real limits.
	 */
	public function inspect(string $zipPath): bool
	{
		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
			throw new RuntimeException('Unable to open archive.');
		}

		try {
			if ($zip->numFiles > $this->maxEntries) {
				return false;
			}

			$totalUncompressed = 0;
			$totalCompressed = 0;

			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				if ($stat === false) {
					return false;
				}

				if ($stat['size'] > $this->maxFileSize) {
					return false;
				}

				$totalUncompressed += $stat['size'];
				$totalCompressed += $stat['comp_size'];

				if ($totalUncompressed > $this->maxTotalSize) {
					return false;
				}
			}

			if ($totalCompressed > 0
				&& ($totalUncompressed / $totalCompressed) > $this->maxRatio) {
				return false;
			}

			return true;
		} finally {
			$zip->close();
		}
	}

	/**
	 * Safely extract the archive to $destDir.
	 * Throws RuntimeException the moment anything looks like an attack.
	 */
	public function extract(string $zipPath, string $destDir): void
	{
		if (!$this->inspect($zipPath)) {
			throw new ZipBombDetectedException('Archive rejected by pre-flight inspection.');
		}

		if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
			throw new RuntimeException('Cannot create destination directory.');
		}
		$baseReal = realpath($destDir);
		if ($baseReal === false) {
			throw new RuntimeException('Destination directory is invalid.');
		}

		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
			throw new RuntimeException('Unable to open archive.');
		}

		$writtenTotal = 0;

		try {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				$name = $stat['name'];

				// Skip directory entries after resolving their safe path.
				$target = $this->resolveSafeTarget($baseReal, $name);
				if ($target === null) {
					throw new RuntimeException("Blocked path traversal: {$name}");
				}

				if (str_ends_with($name, '/')) {
					if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
						throw new RuntimeException("Cannot create directory: {$name}");
					}
					continue;
				}

				$dir = dirname($target);
				if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
					throw new RuntimeException("Cannot create directory for: {$name}");
				}

				$in = $zip->getStream($name);
				if ($in === false) {
					throw new RuntimeException("Cannot read entry: {$name}");
				}

				$out = fopen($target, 'wb');
				if ($out === false) {
					fclose($in);
					throw new RuntimeException("Cannot write file: {$name}");
				}

				$fileWritten = 0;

				try {
					while (!feof($in)) {
						$chunk = fread($in, $this->chunkSize);
						if ($chunk === false) {
							throw new RuntimeException("Read error on: {$name}");
						}

						$len = strlen($chunk);
						$fileWritten += $len;
						$writtenTotal += $len;

						// The real, non-negotiable limits — measured from
						// bytes actually produced, not from the header.
						if ($fileWritten > $this->maxFileSize) {
							throw new ZipBombDetectedException("Entry exceeds per-file limit: {$name}");
						}
						if ($writtenTotal > $this->maxTotalSize) {
							throw new ZipBombDetectedException('Archive exceeds total size limit.');
						}

						if (fwrite($out, $chunk) === false) {
							throw new RuntimeException("Write error on: {$name}");
						}
					}
				} catch (Throwable $e) {
					fclose($in);
					fclose($out);
					@unlink($target);   // don't leave a partial bomb on disk
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
	 * $baseReal, or null if the entry tries to escape (zip slip).
	 */
	private function resolveSafeTarget(string $baseReal, string $entryName): ?string
	{
		if ($entryName === '' || str_contains($entryName, "\0")) {
			return null;
		}

		// Normalise separators and strip any leading slashes / drive letters.
		$entry = str_replace('\\', '/', $entryName);
		$entry = ltrim($entry, '/');

		$parts = [];
		foreach (explode('/', $entry) as $segment) {
			if ($segment === '' || $segment === '.') {
				continue;
			}
			if ($segment === '..') {
				if (empty($parts)) {
					return null; // trying to climb above base
				}
				array_pop($parts);
				continue;
			}
			$parts[] = $segment;
		}

		if (empty($parts)) {
			return null;
		}

		$target = $baseReal . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);

		// Final belt-and-braces prefix check.
		if (!str_starts_with($target, $baseReal . DIRECTORY_SEPARATOR)) {
			return null;
		}

		return $target;
	}
}
