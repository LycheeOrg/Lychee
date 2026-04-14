<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Admin;

use App\Actions\Album\SetProtectionPolicy;
use App\Enum\AspectRatioType;
use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\LicenseType;
use App\Enum\OrderSortingType;
use App\Enum\PhotoLayoutType;
use App\Enum\TimelineAlbumGranularity;
use App\Enum\TimelinePhotoGranularity;
use App\Http\Resources\Models\Utils\AlbumProtectionPolicy;
use App\Models\Album;
use App\Models\BaseAlbumImpl;

/**
 * Applies a partial set of metadata and/or visibility changes to a batch of albums.
 *
 * Changes are applied within the caller's DB transaction.
 * Fields absent from the payload are left unchanged.
 *
 * Three groups are processed separately:
 *   1. base_albums columns  → chunked mass UPDATE via BaseAlbumImpl
 *   2. albums columns       → chunked mass UPDATE via Album
 *   3. Visibility fields    → per-album via SetProtectionPolicy::do()
 */
class BulkEditAlbumsAction
{
	private SetProtectionPolicy $set_protection_policy;

	public function __construct()
	{
		$this->set_protection_policy = new SetProtectionPolicy();
	}

	/**
	 * Apply the partial payload to all specified album IDs.
	 *
	 * @param string[]             $album_ids existing Album IDs
	 * @param array<string, mixed> $payload   Validated, keyed by field name. Only present keys are applied.
	 */
	public function do(array $album_ids, array $payload): void
	{
		// ── Group 1: base_albums columns ─────────────────────────────────────
		$base_data = [];

		if (array_key_exists('description', $payload)) {
			$base_data['description'] = $payload['description'];
		}
		if (array_key_exists('copyright', $payload)) {
			$base_data['copyright'] = $payload['copyright'];
		}
		if (array_key_exists('photo_layout', $payload)) {
			$base_data['photo_layout'] = $payload['photo_layout'] instanceof PhotoLayoutType
				? $payload['photo_layout']->value
				: $payload['photo_layout'];
		}
		if (array_key_exists('photo_sorting_col', $payload)) {
			$base_data['sorting_col'] = $payload['photo_sorting_col'] instanceof ColumnSortingPhotoType
				? $payload['photo_sorting_col']->value
				: $payload['photo_sorting_col'];
		}
		if (array_key_exists('photo_sorting_order', $payload)) {
			$base_data['sorting_order'] = $payload['photo_sorting_order'] instanceof OrderSortingType
				? $payload['photo_sorting_order']->value
				: $payload['photo_sorting_order'];
		}
		if (array_key_exists('photo_timeline', $payload)) {
			$base_data['photo_timeline'] = $payload['photo_timeline'] instanceof TimelinePhotoGranularity
				? $payload['photo_timeline']->value
				: $payload['photo_timeline'];
		}
		if (array_key_exists('is_nsfw', $payload)) {
			$base_data['is_nsfw'] = $payload['is_nsfw'];
		}

		if ($base_data !== []) {
			BaseAlbumImpl::query()
				->whereIn('id', $album_ids)
				->update($base_data);
		}

		// ── Group 2: albums columns ───────────────────────────────────────────
		$album_data = [];

		if (array_key_exists('license', $payload)) {
			$album_data['license'] = $payload['license'] instanceof LicenseType
				? $payload['license']->value
				: $payload['license'];
		}
		if (array_key_exists('album_thumb_aspect_ratio', $payload)) {
			$album_data['album_thumb_aspect_ratio'] = $payload['album_thumb_aspect_ratio'] instanceof AspectRatioType
				? $payload['album_thumb_aspect_ratio']->value
				: $payload['album_thumb_aspect_ratio'];
		}
		if (array_key_exists('album_timeline', $payload)) {
			$album_data['album_timeline'] = $payload['album_timeline'] instanceof TimelineAlbumGranularity
				? $payload['album_timeline']->value
				: $payload['album_timeline'];
		}
		if (array_key_exists('album_sorting_col', $payload)) {
			$album_data['album_sorting_col'] = $payload['album_sorting_col'] instanceof ColumnSortingAlbumType
				? $payload['album_sorting_col']->value
				: $payload['album_sorting_col'];
		}
		if (array_key_exists('album_sorting_order', $payload)) {
			$album_data['album_sorting_order'] = $payload['album_sorting_order'] instanceof OrderSortingType
				? $payload['album_sorting_order']->value
				: $payload['album_sorting_order'];
		}

		if ($album_data !== []) {
			Album::query()
				->whereIn('id', $album_ids)
				->update($album_data);
		}

		// ── Group 3: Visibility fields ────────────────────────────────────────
		$visibility_keys = ['is_public', 'is_link_required', 'grants_full_photo_access', 'grants_download', 'grants_upload'];
		$has_visibility = false;
		foreach ($visibility_keys as $key) {
			if (array_key_exists($key, $payload)) {
				$has_visibility = true;
				break;
			}
		}

		if ($has_visibility) {
			/** @var Album[] $albums */
			$albums = Album::query()
				->with('base_class.access_permissions')
				->whereIn('id', $album_ids)
				->get()
				->all();

			foreach ($albums as $album) {
				$existing = $album->public_permissions();

				// Derive current values as defaults, then overlay payload
				$is_public = array_key_exists('is_public', $payload)
					? filter_var($payload['is_public'], FILTER_VALIDATE_BOOLEAN)
					: ($existing !== null);
				$is_link_required = array_key_exists('is_link_required', $payload)
					? filter_var($payload['is_link_required'], FILTER_VALIDATE_BOOLEAN)
					: ($existing?->is_link_required === true);
				$grants_full_photo_access = array_key_exists('grants_full_photo_access', $payload)
					? filter_var($payload['grants_full_photo_access'], FILTER_VALIDATE_BOOLEAN)
					: ($existing?->grants_full_photo_access === true);
				$grants_download = array_key_exists('grants_download', $payload)
					? filter_var($payload['grants_download'], FILTER_VALIDATE_BOOLEAN)
					: ($existing?->grants_download === true);
				$grants_upload = array_key_exists('grants_upload', $payload)
					? filter_var($payload['grants_upload'], FILTER_VALIDATE_BOOLEAN)
					: ($existing?->grants_upload === true);

				// is_nsfw may have been updated in group 1; re-read from model
				// but group 1 used a direct mass-update so we need the payload value if available.
				$is_nsfw = array_key_exists('is_nsfw', $payload)
					? filter_var($payload['is_nsfw'], FILTER_VALIDATE_BOOLEAN)
					: ($album->is_nsfw === true);

				$protection_policy = new AlbumProtectionPolicy(
					is_public: $is_public,
					is_link_required: $is_link_required,
					is_nsfw: $is_nsfw,
					grants_full_photo_access: $grants_full_photo_access,
					grants_download: $grants_download,
					grants_upload: $grants_upload,
				);

				$this->set_protection_policy->do($album, $protection_policy, false, null);
			}
		}
	}
}
