<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TimelineAlbum.
 *
 * Models the cross-album, library-wide "all photos by date" scope served by
 * the v8 Timeline (`/timeline`) - reachable as `album_id='timeline'` through
 * the existing v3 photo-tier routes ({@see \App\Http\Controllers\Gallery\AlbumListing\PhotoChildrenController}).
 *
 * Unlike every other {@see BaseSmartAlbum}, {@see self::photos()} is fully
 * overridden rather than relying on the inherited implementation: Timeline
 * has always had its own, longstanding visibility/sorting/access rules
 * (`hide_nsfw_in_timeline`, `timeline_photos_order`,
 * `timeline_photos_public`/`timeline_page_enabled`) which predate — and
 * deliberately diverge from — `BaseSmartAlbum`'s own
 * `hide_nsfw_in_smart_albums`/`enable_smart_album_per_owner`/`may_upload`
 * gates. See spec.md Decision Card Q-066-01 for the full rationale.
 *
 * `photos()` reimplements {@see \App\Actions\Photo\Timeline::do()}'s exact
 * query (visibility/NSFW predicate only - ordering is applied by the v3
 * tier queries via {@see \App\Actions\Photo\StructOfArrays\ResolvesPhotoSource::resolveEffectiveSorting()},
 * not here, mirroring every other {@see BaseSmartAlbum}).
 */
class TimelineAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::TIMELINE->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::TIMELINE,
			// photos() is fully overridden below - this condition is never
			// evaluated, but BaseSmartAlbum's constructor requires one.
			smart_condition: function (Builder $query): void {
			}
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Reimplements {@see \App\Actions\Photo\Timeline::do()}'s exact query:
	 * no per-owner restriction (`origin: null`), NSFW visibility gated by
	 * the Timeline-specific `hide_nsfw_in_timeline` config key - never
	 * `BaseSmartAlbum::photos()`'s `hide_nsfw_in_smart_albums`/
	 * `enable_smart_album_per_owner`.
	 *
	 * {@inheritDoc}
	 */
	public function photos(): Builder
	{
		$user = $this->resolveUser();
		$unlocked_album_ids = AlbumPolicy::getUnlockedAlbumIDs();

		return $this->photo_query_policy->applySearchabilityFilter(
			query: Photo::query()->with(['size_variants', 'statistics', 'palette', 'tags', 'rating']),
			user: $user,
			unlocked_album_ids: $unlocked_album_ids,
			origin: null,
			include_nsfw: !$this->config_manager->getValueAsBool('hide_nsfw_in_timeline')
		);
	}
}
