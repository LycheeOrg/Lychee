<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Services\Zip;

use App\Services\Zip\SafeZipExtractor;
use App\Services\Zip\ZipBombDetectedException;
use RuntimeException;
use Tests\AbstractTestCase;
use ZipArchive;

class SafeZipExtractorTest extends AbstractTestCase
{
	/** @var string[] paths to remove in tearDown */
	private array $cleanupPaths = [];

	protected function tearDown(): void
	{
		foreach ($this->cleanupPaths as $path) {
			$this->removePath($path);
		}
		$this->cleanupPaths = [];

		parent::tearDown();
	}

	private function removePath(string $path): void
	{
		if (is_file($path) || is_link($path)) {
			@unlink($path);

			return;
		}
		if (!is_dir($path)) {
			return;
		}
		foreach (scandir($path) as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$this->removePath($path . DIRECTORY_SEPARATOR . $entry);
		}
		@rmdir($path);
	}

	/**
	 * Creates a temporary destination directory path (not yet created on disk).
	 */
	private function makeDestDir(): string
	{
		$path = tempnam(sys_get_temp_dir(), 'safezip_dest_');
		unlink($path);
		$this->cleanupPaths[] = $path;

		return $path;
	}

	/**
	 * Builds a normal, honestly-declared zip archive from a name => content map.
	 * When $compress is true, entries are forced to use DEFLATE so comp_size < size,
	 * which is needed to exercise the compression-ratio check.
	 *
	 * @param array<string,string> $entries
	 */
	private function makeZip(array $entries, bool $compress = false): string
	{
		$path = tempnam(sys_get_temp_dir(), 'safezip_src_') . '.zip';
		$this->cleanupPaths[] = $path;

		$zip = new ZipArchive();
		$zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		foreach ($entries as $name => $content) {
			if (str_ends_with($name, '/')) {
				$zip->addEmptyDir(rtrim($name, '/'));
				continue;
			}
			$zip->addFromString($name, $content);
			if ($compress) {
				$zip->setCompressionName($name, ZipArchive::CM_DEFLATE, 9);
			}
		}
		$zip->close();

		return $path;
	}

	/**
	 * Builds a hand-crafted zip archive whose DEFLATE-compressed entry, once
	 * decompressed, yields far more bytes than the declared uncompressed size
	 * in its local/central headers claims. This simulates a zip bomb that
	 * "lies" in its header to defeat naive stat()-based pre-flight checks.
	 *
	 * PHP's ZipArchive::statIndex() trusts the declared (lying) size, but
	 * streaming the entry via getStream() decodes the real deflate bitstream,
	 * which is self-terminating and independent of the declared size field.
	 */
	private function makeLyingZip(string $name, string $realContent, int $declaredUncompressedSize): string
	{
		$compressed = gzdeflate($realContent, 9);
		$crc = crc32($realContent);

		$localHeader = "PK\x03\x04"
			. pack('v', 20)
			. pack('v', 0)
			. pack('v', 8) // method = deflate
			. pack('v', 0)
			. pack('v', 0x21)
			. pack('V', $crc)
			. pack('V', strlen($compressed))
			. pack('V', $declaredUncompressedSize)
			. pack('v', strlen($name))
			. pack('v', 0);

		$localRecord = $localHeader . $name . $compressed;

		$centralHeader = "PK\x01\x02"
			. pack('v', 20)
			. pack('v', 20)
			. pack('v', 0)
			. pack('v', 8)
			. pack('v', 0)
			. pack('v', 0x21)
			. pack('V', $crc)
			. pack('V', strlen($compressed))
			. pack('V', $declaredUncompressedSize)
			. pack('v', strlen($name))
			. pack('v', 0)
			. pack('v', 0)
			. pack('v', 0)
			. pack('v', 0)
			. pack('V', 0)
			. pack('V', 0)
			. $name;

		$eocd = "PK\x05\x06"
			. pack('v', 0)
			. pack('v', 0)
			. pack('v', 1)
			. pack('v', 1)
			. pack('V', strlen($centralHeader))
			. pack('V', strlen($localRecord))
			. pack('v', 0);

		$path = tempnam(sys_get_temp_dir(), 'safezip_lie_') . '.zip';
		$this->cleanupPaths[] = $path;
		file_put_contents($path, $localRecord . $centralHeader . $eocd);

		return $path;
	}

	public function testInspectAcceptsArchiveWithinLimits(): void
	{
		$zip = $this->makeZip(['a.txt' => 'hello', 'b.txt' => 'world']);
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 500, maxEntries: 10, maxRatio: 1000);

		self::assertTrue($extractor->inspect($zip));
	}

	public function testInspectRejectsTooManyEntries(): void
	{
		$entries = [];
		for ($i = 0; $i < 5; $i++) {
			$entries["file{$i}.txt"] = 'x';
		}
		$zip = $this->makeZip($entries);
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 500, maxEntries: 3, maxRatio: 1000);

		self::assertFalse($extractor->inspect($zip));
	}

	public function testInspectRejectsSingleFileTooLarge(): void
	{
		$zip = $this->makeZip(['big.txt' => str_repeat('x', 200)]);
		$extractor = new SafeZipExtractor(maxTotalSize: 10000, maxFileSize: 100, maxEntries: 10, maxRatio: 1000);

		self::assertFalse($extractor->inspect($zip));
	}

	public function testInspectRejectsTotalSizeTooLarge(): void
	{
		$zip = $this->makeZip([
			'a.txt' => str_repeat('x', 60),
			'b.txt' => str_repeat('x', 60),
		]);
		$extractor = new SafeZipExtractor(maxTotalSize: 100, maxFileSize: 1000, maxEntries: 10, maxRatio: 1000);

		self::assertFalse($extractor->inspect($zip));
	}

	public function testInspectRejectsHighCompressionRatio(): void
	{
		// Highly repetitive content compresses extremely well.
		$zip = $this->makeZip(['bomb.txt' => str_repeat('A', 200_000)], compress: true);
		$extractor = new SafeZipExtractor(maxTotalSize: PHP_INT_MAX, maxFileSize: PHP_INT_MAX, maxEntries: 10, maxRatio: 10);

		self::assertFalse($extractor->inspect($zip));
	}

	public function testInspectAllowsHighRatioWhenUnderThreshold(): void
	{
		$zip = $this->makeZip(['bomb.txt' => str_repeat('A', 200_000)], compress: true);
		// Same archive, but the ratio cap is generous enough to allow it.
		$extractor = new SafeZipExtractor(maxTotalSize: PHP_INT_MAX, maxFileSize: PHP_INT_MAX, maxEntries: 10, maxRatio: 100_000);

		self::assertTrue($extractor->inspect($zip));
	}

	public function testInspectThrowsWhenArchiveCannotBeOpened(): void
	{
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 1000, maxEntries: 10, maxRatio: 1000);

		self::expectException(RuntimeException::class);
		$extractor->inspect('/does/not/exist.zip');
	}

	public function testExtractWritesFilesWithCorrectContent(): void
	{
		$zip = $this->makeZip([
			'a.txt' => 'hello',
			'sub/b.txt' => 'world',
		]);
		$dest = $this->makeDestDir();
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 500, maxEntries: 10, maxRatio: 1000);

		$extractor->extract($zip, $dest);

		self::assertFileExists($dest . '/a.txt');
		self::assertFileExists($dest . '/sub/b.txt');
		self::assertEquals('hello', file_get_contents($dest . '/a.txt'));
		self::assertEquals('world', file_get_contents($dest . '/sub/b.txt'));
	}

	/**
	 * An entry with a leading '/' is not rejected outright: its leading slash
	 * is stripped and the remainder is treated as a path relative to the
	 * destination, so it stays safely sandboxed inside $destDir rather than
	 * escaping to the real filesystem root.
	 */
	public function testExtractNeutralizesAbsolutePathIntoDestDir(): void
	{
		$zip = $this->makeZip(['/etc/evil.txt' => 'pwned']);
		$dest = $this->makeDestDir();
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 500, maxEntries: 10, maxRatio: 1000);

		$extractor->extract($zip, $dest);

		self::assertFileExists($dest . '/etc/evil.txt');
		self::assertFileDoesNotExist('/etc/evil.txt');
	}

	/**
	 * Path traversal is a distinct attack from a zip bomb: it must still raise
	 * a plain RuntimeException, not the more specific ZipBombDetectedException,
	 * so callers that only want to react to actual bombs (e.g. delete the
	 * rejected file) don't misfire on a zip-slip attempt.
	 */
	public function testExtractRejectsDotDotPathTraversal(): void
	{
		$zip = $this->makeZip(['../evil.txt' => 'pwned']);
		$dest = $this->makeDestDir();
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 500, maxEntries: 10, maxRatio: 1000);

		try {
			$extractor->extract($zip, $dest);
			self::fail('Expected a RuntimeException to be thrown.');
		} catch (RuntimeException $e) {
			self::assertNotInstanceOf(ZipBombDetectedException::class, $e);
		}

		self::assertFileDoesNotExist(dirname($dest) . '/evil.txt');
	}

	public function testExtractRejectsWhenPreflightFails(): void
	{
		$zip = $this->makeZip(['big.txt' => str_repeat('x', 200)]);
		$dest = $this->makeDestDir();
		$extractor = new SafeZipExtractor(maxTotalSize: 1000, maxFileSize: 100, maxEntries: 10, maxRatio: 1000);

		self::expectException(ZipBombDetectedException::class);
		$extractor->extract($zip, $dest);
	}

	/**
	 * The headline defense-in-depth behaviour: a zip entry whose header lies
	 * about its uncompressed size (fooling the cheap pre-flight inspection)
	 * must still be caught by the streamed, real-byte-counting extraction,
	 * and any partial output must be cleaned up rather than left on disk.
	 */
	public function testExtractAbortsOnRealBytesExceedingDeclaredHeaderSize(): void
	{
		$realContent = str_repeat('A', 100_000); // 100,000 real bytes
		$zip = $this->makeLyingZip('evil.bin', $realContent, declaredUncompressedSize: 100); // lies: claims 100 bytes
		$dest = $this->makeDestDir();

		// The declared size (100) comfortably passes inspection...
		$extractor = new SafeZipExtractor(maxTotalSize: 10_000, maxFileSize: 1_000, maxEntries: 10, maxRatio: 1_000_000);
		self::assertTrue($extractor->inspect($zip));

		// ...but the real, streamed bytes (100,000) must still trip the per-file limit.
		self::expectException(ZipBombDetectedException::class);

		try {
			$extractor->extract($zip, $dest);
		} finally {
			self::assertFileDoesNotExist($dest . '/evil.bin');
		}
	}
}
