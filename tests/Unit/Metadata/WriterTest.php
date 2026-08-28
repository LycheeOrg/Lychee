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

namespace Tests\Unit\Metadata;

use App\DTO\MetadataWritePayload;
use App\Exceptions\ExternalComponentFailedException;
use App\Image\Files\NativeLocalFile;
use App\Metadata\Writer;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\AbstractTestCase;

/**
 * Covers Feature 059's App\Metadata\Writer: the exact `exiftool` argument
 * array built per field (FR-059-08), and NFR-059-01 (no shell-string
 * interpolation of user-controlled text).
 */
class WriterTest extends AbstractTestCase
{
	private const EXIFTOOL_PATH = '/usr/bin/exiftool';

	public function testEmbedsFullFieldSetAsArray(): void
	{
		Process::fake();

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(
			title: 'My Title',
			description: 'My Description',
			tags: ['tag1', 'tag2'],
			rating: 4,
		);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);

		Process::assertRan(function (PendingProcess $process) {
			$command = $process->command;
			self::assertIsArray($command);
			self::assertSame(self::EXIFTOOL_PATH, $command[0]);
			self::assertContains('-overwrite_original', $command);
			self::assertContains('-P', $command);
			self::assertContains('-XMP-dc:Title=My Title', $command);
			self::assertContains('-IPTC:ObjectName=My Title', $command);
			self::assertContains('-EXIF:XPTitle=My Title', $command);
			self::assertContains('-EXIF:ImageDescription=My Description', $command);
			self::assertContains('-IPTC:Caption-Abstract=My Description', $command);
			self::assertContains('-XMP-dc:Description=My Description', $command);
			self::assertContains('-IPTC:Keywords=', $command);
			self::assertContains('-XMP-dc:Subject=', $command);
			self::assertContains('-IPTC:Keywords+=tag1', $command);
			self::assertContains('-IPTC:Keywords+=tag2', $command);
			self::assertContains('-XMP-dc:Subject+=tag1', $command);
			self::assertContains('-XMP-dc:Subject+=tag2', $command);
			self::assertContains('-EXIF:XPKeywords=tag1;tag2', $command);
			self::assertContains('-XMP-xmp:Rating=4', $command);
			self::assertContains('-EXIF:Rating=4', $command);
			self::assertContains('-EXIF:RatingPercent=80', $command);
			self::assertSame(__FILE__, end($command));

			return true;
		});
	}

	public function testEmptyTagsOnlyClearsListTags(): void
	{
		Process::fake();

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: null, description: null, tags: [], rating: null);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);

		Process::assertRan(function (PendingProcess $process) {
			$command = $process->command;
			self::assertContains('-IPTC:Keywords=', $command);
			self::assertContains('-XMP-dc:Subject=', $command);
			self::assertNotContains('-IPTC:Keywords+=', $command);
			self::assertContains('-EXIF:XPKeywords=', $command);

			return true;
		});
	}

	public function testNullTitleAndDescriptionClearTags(): void
	{
		Process::fake();

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: null, description: null, tags: [], rating: null);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);

		Process::assertRan(function (PendingProcess $process) {
			$command = $process->command;
			self::assertContains('-XMP-dc:Title=', $command);
			self::assertContains('-IPTC:ObjectName=', $command);
			self::assertContains('-EXIF:XPTitle=', $command);
			self::assertContains('-EXIF:ImageDescription=', $command);
			self::assertContains('-IPTC:Caption-Abstract=', $command);
			self::assertContains('-XMP-dc:Description=', $command);

			return true;
		});
	}

	public function testNullRatingEmitsEmptyValueAssignmentsToDelete(): void
	{
		Process::fake();

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: null, description: null, tags: [], rating: null);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);

		Process::assertRan(function (PendingProcess $process) {
			$command = $process->command;
			self::assertContains('-XMP-xmp:Rating=', $command);
			self::assertContains('-EXIF:Rating=', $command);
			self::assertContains('-EXIF:RatingPercent=', $command);
			self::assertNotContains('-XMP-xmp:Rating=4', $command);

			return true;
		});
	}

	/**
	 * NFR-059-01: a value containing shell metacharacters must be embedded
	 * verbatim as literal argument text, never interpolated into a shell
	 * string. Since `Process::run()` is invoked with an array (never a
	 * string), there is no shell to escape from in the first place —
	 * asserted here by confirming the raw, unescaped value appears intact
	 * as a single array element.
	 */
	public function testShellMetacharactersAreEmbeddedVerbatimNeverExecuted(): void
	{
		Process::fake();

		$dangerous = 'My `photo`; $(rm -rf /) "quoted" \'quoted\'';
		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: $dangerous, description: null, tags: [$dangerous], rating: null);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);

		Process::assertRan(function (PendingProcess $process) use ($dangerous) {
			$command = $process->command;
			self::assertIsArray($command);
			self::assertContains('-XMP-dc:Title=' . $dangerous, $command);
			self::assertContains('-IPTC:Keywords+=' . $dangerous, $command);

			return true;
		});
	}

	/**
	 * The `exiftool_path` config is empty by default (see the
	 * `2024_07_01_231053_path_for_exiftool` migration) and is only ever set
	 * when an admin overrides it. `ConfigManager::hasExiftool()` detects
	 * availability separately via `command -v exiftool`, so it's possible to
	 * reach here with a detected-but-unconfigured exiftool: an empty string
	 * must resolve to the bare binary name for `$PATH` lookup, never be
	 * passed straight through as an empty Process argv[0] (which throws a
	 * Symfony `ValueError`).
	 */
	public function testEmptyExiftoolPathFallsBackToBareBinaryName(): void
	{
		Process::fake();

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: 'x', description: null, tags: [], rating: null);

		(new Writer())->embed($file, $payload, '');

		Process::assertRan(function (PendingProcess $process) {
			self::assertSame('exiftool', $process->command[0]);

			return true;
		});
	}

	public function testNonZeroExitCodeThrows(): void
	{
		Process::fake([
			'*' => Process::result(output: '', errorOutput: 'bad tag', exitCode: 1),
		]);

		$file = new NativeLocalFile(__FILE__);
		$payload = new MetadataWritePayload(title: 'x', description: null, tags: [], rating: null);

		$this->expectException(ExternalComponentFailedException::class);

		(new Writer())->embed($file, $payload, self::EXIFTOOL_PATH);
	}
}
