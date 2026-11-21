<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\DTO\UploadStatusEntry;
use App\Http\Resources\Models\UploadStatusResource;
use App\Models\Photo;
use App\Services\UploadStatusStore;
use Illuminate\Support\Collection;

/**
 * Answer the upload statuses of a batch of provisional IDs (Feature 116, FR-116-06, FR-116-07).
 *
 * One cache read for the batch and one primary-key lookup for the photo IDs (NFR-116-01).
 * An entry the caller may not read answers like a missing one (NFR-116-04).
 */
class ResolveUploadStatuses
{
	public function __construct(private readonly UploadStatusStore $store)
	{
	}

	/**
	 * @param string[] $provisional_ids
	 * @param int|null $user_id         the caller
	 *
	 * @return Collection<int,UploadStatusResource> one item per ID, in the given order
	 */
	public function do(array $provisional_ids, ?int $user_id): Collection
	{
		$entries = array_map(
			fn (?UploadStatusEntry $entry) => $entry?->isReadableBy($user_id) === true ? $entry : null,
			$this->store->many($provisional_ids)
		);
		$existing_photo_ids = $this->existingPhotoIds($entries);

		return collect(array_values($provisional_ids))
			->map(fn (string $id, int $index) => UploadStatusResource::of($id, $entries[$index], $existing_photo_ids));
	}

	/**
	 * The recorded photo IDs that still exist, in one primary-key lookup.
	 *
	 * No visibility check (FR-116-07): the uploader sent the same file, so the ID of a
	 * duplicate reveals nothing the status has not already said, and photo routes apply their own policy.
	 *
	 * @param array<int,UploadStatusEntry|null> $entries
	 *
	 * @return array<string,true>
	 */
	private function existingPhotoIds(array $entries): array
	{
		$photo_ids = array_values(array_unique(array_filter(array_map(fn (?UploadStatusEntry $entry) => $entry?->photo_id, $entries), fn (?string $id) => $id !== null)));
		if (count($photo_ids) === 0) {
			return [];
		}

		/** @var array<string,true> $existing */
		$existing = Photo::query()
			->toBase()
			->whereIn('id', $photo_ids)
			->pluck('id')
			->mapWithKeys(fn (string $id) => [$id => true])
			->all();

		return $existing;
	}
}
