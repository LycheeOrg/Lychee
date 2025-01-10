<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Feature_v1\LibUnitTests;

use App\Image\Files\InMemoryBuffer;
use App\Image\Files\TemporaryLocalFile;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;
use function Safe\fwrite;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssertableZipArchive extends \ZipArchive
{
	public const ZIP_STAT_SIZE = 'size';

	/**
	 * Creates an assertable ZIP archive from the given response.
	 *
	 * @param TestResponse<\Illuminate\Http\JsonResponse> $response
	 *
	 * @return self
	 */
	public static function createFromResponse(TestResponse $response): self
	{
		$memoryBlob = new InMemoryBuffer();
		fwrite(
			$memoryBlob->stream(),
			// @phpstan-ignore-next-line
			$response->baseResponse instanceof StreamedResponse ? $response->streamedContent() : $response->content()
		);
		$tmpZipFile = new TemporaryLocalFile('.zip', 'archive');
		$tmpZipFile->write($memoryBlob->read());
		$memoryBlob->close();

		$zipArchive = new self();
		$zipArchive->open($tmpZipFile->getRealPath());

		return $zipArchive;
	}

	/**
	 * Asserts that the ZIP archive contains a file of the given name and size.
	 *
	 * @param string   $fileName         the name of the expected file
	 * @param int|null $expectedFileSize (optional) the expected file size of the uncompressed file
	 *
	 * @return void
	 */
	public function assertContainsFile(string $fileName, ?int $expectedFileSize): void
	{
		$stat = $this->statName($fileName);
		PHPUnit::assertNotFalse($stat, 'Could not assert that ZIP archive contains ' . $fileName);
		if ($expectedFileSize !== null) {
			PHPUnit::assertEquals($expectedFileSize, $stat[self::ZIP_STAT_SIZE]);
		}
	}

	/**
	 * Asserts that the ZIP archive contains exactly the given files and no more.
	 *
	 * @param array<string, array{size?: ?int}> $expectedFiles a list of expected file names together with optional file attributes
	 *
	 * @return void
	 */
	public function assertContainsFilesExactly(array $expectedFiles): void
	{
		PHPUnit::assertCount(count($expectedFiles), $this);
		foreach ($expectedFiles as $fileName => $fileStat) {
			$this->assertContainsFile(
				$fileName,
				key_exists(self::ZIP_STAT_SIZE, $fileStat) ? $fileStat[self::ZIP_STAT_SIZE] : null
			);
		}
	}
}
