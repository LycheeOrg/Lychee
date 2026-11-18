<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

use App\Enum\UploadStatus;
use App\Exceptions\BaseLycheeException;
use App\Exceptions\PhotoRejectedException;
use App\Exceptions\PhotoSkippedException;

/**
 * Status of one upload as kept in the cache under its provisional ID (Feature 116, DO-116-02).
 *
 * Pure value object: it maps outcomes to entries and decides who may read them (ADR-116-01).
 */
final readonly class UploadStatusEntry
{
	/** Message of a failure whose exception is not one of Lychee's user-facing exceptions. */
	public const GENERIC_FAILURE = 'Processing failed';

	public function __construct(
		public ?int $uploader_id,
		public UploadStatus $status,
		public ?string $photo_id = null,
		public ?string $message = null,
	) {
	}

	public static function processing(?int $uploader_id): self
	{
		return new self($uploader_id, UploadStatus::PROCESSING);
	}

	/**
	 * @param string $photo_id     the photo in the target album
	 * @param bool   $is_duplicate whether that photo already existed before the upload
	 */
	public static function ofPhoto(?int $uploader_id, string $photo_id, bool $is_duplicate): self
	{
		return new self($uploader_id, $is_duplicate ? UploadStatus::DUPLICATE : UploadStatus::COMPLETED, $photo_id);
	}

	/**
	 * Skips and rejections keep the duplicate's ID and their message.
	 * Lychee's exceptions carry user-facing messages; any other message stays in the logs.
	 */
	public static function ofThrowable(?int $uploader_id, \Throwable $th): self
	{
		return match (true) {
			$th instanceof PhotoSkippedException, $th instanceof PhotoRejectedException => new self($uploader_id, UploadStatus::SKIPPED, $th->photo_id, $th->getMessage()),
			$th instanceof BaseLycheeException => new self($uploader_id, UploadStatus::FAILED, null, $th->getMessage()),
			default => new self($uploader_id, UploadStatus::FAILED, null, self::GENERIC_FAILURE),
		};
	}

	/**
	 * A user's upload answers only to that user; a guest's upload answers to anyone holding its ID.
	 */
	public function isReadableBy(?int $user_id): bool
	{
		return $this->uploader_id === null || $this->uploader_id === $user_id;
	}

	/**
	 * @return array{uploader_id:?int,status:string,photo_id:?string,message:?string}
	 */
	public function toArray(): array
	{
		return [
			'uploader_id' => $this->uploader_id,
			'status' => $this->status->value,
			'photo_id' => $this->photo_id,
			'message' => $this->message,
		];
	}

	/**
	 * Rebuild an entry read from the cache; anything else gives null.
	 */
	public static function fromArray(mixed $data): ?self
	{
		if (!is_array($data) || !self::hasValidShape($data)) {
			return null;
		}

		/** @var array{uploader_id:?int,status:string,photo_id:?string,message:?string} $data */
		return new self($data['uploader_id'], UploadStatus::from($data['status']), $data['photo_id'], $data['message']);
	}

	/**
	 * @param array<array-key,mixed> $data
	 */
	private static function hasValidShape(array $data): bool
	{
		return (($data['uploader_id'] ?? null) === null || is_int($data['uploader_id'])) &&
			is_string($data['status'] ?? null) &&
			UploadStatus::tryFrom($data['status']) !== null &&
			(($data['photo_id'] ?? null) === null || is_string($data['photo_id'])) &&
			(($data['message'] ?? null) === null || is_string($data['message']));
	}
}
