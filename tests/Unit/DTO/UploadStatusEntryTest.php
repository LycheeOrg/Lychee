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

namespace Tests\Unit\DTO;

use App\DTO\UploadStatusEntry;
use App\Enum\UploadStatus;
use App\Exceptions\PhotoRejectedException;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;
use App\Exceptions\QuotaExceededException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Feature 116: mapping of an upload's outcome to its status entry (NFR-116-05).
 */
class UploadStatusEntryTest extends AbstractTestCase
{
	private const PHOTO_ID = 'abcdefghijklmnopqrstuvwx';

	public function testProcessing(): void
	{
		$entry = UploadStatusEntry::processing(7);

		self::assertSame(7, $entry->uploader_id);
		self::assertSame(UploadStatus::PROCESSING, $entry->status);
		self::assertNull($entry->photo_id);
		self::assertNull($entry->message);
	}

	public function testNewPhotoIsCompleted(): void
	{
		$entry = UploadStatusEntry::ofPhoto(7, self::PHOTO_ID, is_duplicate: false);

		self::assertSame(UploadStatus::COMPLETED, $entry->status);
		self::assertSame(self::PHOTO_ID, $entry->photo_id);
		self::assertNull($entry->message);
	}

	public function testLinkedDuplicateIsDuplicate(): void
	{
		$entry = UploadStatusEntry::ofPhoto(null, self::PHOTO_ID, is_duplicate: true);

		self::assertNull($entry->uploader_id);
		self::assertSame(UploadStatus::DUPLICATE, $entry->status);
		self::assertSame(self::PHOTO_ID, $entry->photo_id);
	}

	/**
	 * @return array<string,array{0:\Throwable,1:string}>
	 */
	public static function skipProvider(): array
	{
		return [
			'skipped' => [new PhotoSkippedException(photo_id: self::PHOTO_ID), PhotoSkippedException::DEFAULT_MESSAGE],
			'resynced' => [new PhotoResyncedException(photo_id: self::PHOTO_ID), PhotoResyncedException::DEFAULT_MESSAGE],
			'moderation' => [new PhotoSkippedException('The photo has been skipped, there is already a copy waiting for moderation', photo_id: self::PHOTO_ID), 'The photo has been skipped, there is already a copy waiting for moderation'],
			'rejected' => [new PhotoRejectedException(photo_id: self::PHOTO_ID), PhotoRejectedException::DEFAULT_MESSAGE],
		];
	}

	#[DataProvider('skipProvider')]
	public function testSkipsAndRejectionsAreSkipped(\Throwable $th, string $message): void
	{
		$entry = UploadStatusEntry::ofThrowable(7, $th);

		self::assertSame(7, $entry->uploader_id);
		self::assertSame(UploadStatus::SKIPPED, $entry->status);
		self::assertSame(self::PHOTO_ID, $entry->photo_id);
		self::assertSame($message, $entry->message);
	}

	public function testSkipWithoutPhotoIdKeepsNull(): void
	{
		$entry = UploadStatusEntry::ofThrowable(7, new PhotoSkippedException());

		self::assertSame(UploadStatus::SKIPPED, $entry->status);
		self::assertNull($entry->photo_id);
	}

	public function testLycheeExceptionFailsWithItsMessage(): void
	{
		$entry = UploadStatusEntry::ofThrowable(7, new QuotaExceededException());

		self::assertSame(UploadStatus::FAILED, $entry->status);
		self::assertNull($entry->photo_id);
		self::assertSame((new QuotaExceededException())->getMessage(), $entry->message);
	}

	public function testOtherThrowableFailsWithGenericMessage(): void
	{
		$entry = UploadStatusEntry::ofThrowable(7, new \RuntimeException('SQLSTATE[HY000] /var/www/secret/path'));

		self::assertSame(UploadStatus::FAILED, $entry->status);
		self::assertNull($entry->photo_id);
		self::assertSame(UploadStatusEntry::GENERIC_FAILURE, $entry->message);
	}

	/**
	 * @return array<string,array{0:?int,1:?int,2:bool}>
	 */
	public static function readabilityProvider(): array
	{
		return [
			'uploader reads own entry' => [7, 7, true],
			'other user cannot read' => [7, 8, false],
			'guest cannot read a user entry' => [7, null, false],
			'guest reads a guest entry' => [null, null, true],
			'user reads a guest entry' => [null, 8, true],
		];
	}

	#[DataProvider('readabilityProvider')]
	public function testIsReadableBy(?int $uploader_id, ?int $caller_id, bool $expected): void
	{
		self::assertSame($expected, UploadStatusEntry::processing($uploader_id)->isReadableBy($caller_id));
	}

	public function testArrayRoundTrip(): void
	{
		$entry = UploadStatusEntry::ofThrowable(7, new PhotoSkippedException(photo_id: self::PHOTO_ID));

		$copy = UploadStatusEntry::fromArray($entry->toArray());

		self::assertEquals($entry, $copy);
	}

	/**
	 * @return array<string,array{0:mixed}>
	 */
	public static function malformedProvider(): array
	{
		return [
			'null' => [null],
			'string' => ['processing'],
			'missing status' => [['uploader_id' => 7, 'photo_id' => null, 'message' => null]],
			'unknown status' => [['uploader_id' => 7, 'status' => 'nope', 'photo_id' => null, 'message' => null]],
			'uploader not int' => [['uploader_id' => '7', 'status' => 'processing', 'photo_id' => null, 'message' => null]],
			'photo id not string' => [['uploader_id' => 7, 'status' => 'completed', 'photo_id' => 12, 'message' => null]],
			'message not string' => [['uploader_id' => 7, 'status' => 'failed', 'photo_id' => null, 'message' => 12]],
		];
	}

	#[DataProvider('malformedProvider')]
	public function testMalformedArrayGivesNull(mixed $data): void
	{
		self::assertNull(UploadStatusEntry::fromArray($data));
	}
}
