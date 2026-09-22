<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Traits;

use App\Http\Resources\Models\PhotoResource;
use App\Models\Photo;
use Illuminate\Support\Collection;

/**
 * @property ?Collection<int,PhotoResource> $photos
 */
trait HasPrepPhotoCollection
{
	/**
	 * Resolves the per-photo downgrade map for a collection, in one query
	 * (Feature 070, FR-070-02).
	 *
	 * Resolved here rather than taken as a constructor argument on purpose: the
	 * boolean these resources used to accept was the defect. Several callers
	 * derived it from the `grants_full_photo_access` *config* — which only seeds
	 * newly created shares — and a single value cannot express a decision that
	 * is per photo anyway. Removing the parameter makes that class of mistake
	 * unrepresentable, and `argument.unknown` turns every stale call site into a
	 * static error.
	 *
	 * Resolving policy inside a resource follows what these classes already did
	 * (`FlowItemResource` calls `Gate::check()`, `TimelineResource::fromData()`
	 * resolved `ConfigManager`), so it introduces no new pattern.
	 *
	 * @param Collection<int,\App\Models\Photo> $photos
	 *
	 * @return array<string,bool> keyed by photo id; `true` means downgrade
	 */
	private function resolveDowngradeMap(Collection $photos): array
	{
		/** @var \App\Models\User|null $user */
		$user = \Illuminate\Support\Facades\Auth::user();

		return resolve(\App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants::class)->downgradeMap($photos, $user);
	}

	/**
	 * Feature 070 (FR-070-08): `$should_downgrade` is a **per-photo map**, not
	 * one boolean for the whole collection.
	 *
	 * Full-resolution access is a property of the individual photo — owner, or
	 * granted by one of the albums it belongs to — so a single value applied to
	 * a whole response cannot express it. Several call sites used to derive that
	 * boolean from the `grants_full_photo_access` *config*, which is only the
	 * seed for newly created shares, and therefore over-granted.
	 *
	 * Build the map with
	 * {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants::downgradeMap()}
	 * (one query per collection). A photo absent from the map is **downgraded**
	 * — deny by default (NFR-070-03).
	 *
	 * @param Collection<int,\App\Models\Photo> $photos
	 * @param array<string,bool>                 $should_downgrade keyed by photo id
	 *
	 * @return Collection<int,PhotoResource>
	 */
	private function toPhotoResources(Collection $photos, ?string $album_id, array $should_downgrade, bool $is_smart_album = false): Collection
	{
		return $photos->map(fn ($photo) => new PhotoResource(
			photo: $photo,
			album_id: $album_id,
			should_downgrade_size_variants: $should_downgrade[$photo->id] ?? true,
			is_smart_album: $is_smart_album,
		));
	}

	private function prepPhotosCollection(): void
	{
		$previous_photo = null;
		$this->photos->each(function (PhotoResource &$photo) use (&$previous_photo): void {
			if ($previous_photo !== null) {
				$previous_photo->next_photo_id = $photo->id;
			}
			$photo->previous_photo_id = $previous_photo?->id;
			$previous_photo = $photo;
		});

		// Photo wrap-around is disabled for now.
		// if ($this->photos->count() > 1 && request()->configs()->getValueAsBool('photos_wraparound')) {
		// 	$this->photos->first()->previous_photo_id = $this->photos->last()->id;
		// 	$this->photos->last()->next_photo_id = $this->photos->first()->id;
		// }
	}
}