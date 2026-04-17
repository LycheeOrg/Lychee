<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Admin;

use App\Actions\Album\SetProtectionPolicy;
use App\DTO\BulkAlbumPatchData;
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
	 * Only fields that were present in the original request (tracked via
	 * {@see BulkAlbumPatchData::has()}) are updated; absent fields are left
	 * unchanged.
	 *
	 * @param BulkAlbumPatchData $data validated, typed patch payload
	 */
	public function do(BulkAlbumPatchData $data): void
	{
		$album_ids = $data->album_ids;

		// ── Group 1: base_albums columns ─────────────────────────────────────
		$base_data = [];

		if ($data->has('description')) {
			$base_data['description'] = $data->description;
		}
		if ($data->has('copyright')) {
			$base_data['copyright'] = $data->copyright;
		}
		if ($data->has('photo_layout')) {
			$base_data['photo_layout'] = $data->photo_layout?->value;
		}
		if ($data->has('photo_sorting_col')) {
			$base_data['sorting_col'] = $data->photo_sorting_col?->value;
		}
		if ($data->has('photo_sorting_order')) {
			$base_data['sorting_order'] = $data->photo_sorting_order?->value;
		}
		if ($data->has('photo_timeline')) {
			$base_data['photo_timeline'] = $data->photo_timeline?->value;
		}
		if ($data->has('is_nsfw')) {
			$base_data['is_nsfw'] = $data->is_nsfw;
		}

		if ($base_data !== []) {
			BaseAlbumImpl::query()
				->whereIn('id', $album_ids)
				->update($base_data);
		}

		// ── Group 2: albums columns ───────────────────────────────────────────
		$album_data = [];

		if ($data->has('license')) {
			$album_data['license'] = $data->license?->value;
		}
		if ($data->has('album_thumb_aspect_ratio')) {
			$album_data['album_thumb_aspect_ratio'] = $data->album_thumb_aspect_ratio?->value;
		}
		if ($data->has('album_timeline')) {
			$album_data['album_timeline'] = $data->album_timeline?->value;
		}
		if ($data->has('album_sorting_col')) {
			$album_data['album_sorting_col'] = $data->album_sorting_col?->value;
		}
		if ($data->has('album_sorting_order')) {
			$album_data['album_sorting_order'] = $data->album_sorting_order?->value;
		}

		if ($album_data !== []) {
			Album::query()
				->whereIn('id', $album_ids)
				->update($album_data);
		}

		// ── Group 3: Visibility fields ────────────────────────────────────────
		$has_visibility = $data->has('is_public') ||
			$data->has('is_link_required') ||
			$data->has('grants_full_photo_access') ||
			$data->has('grants_download') ||
			$data->has('grants_upload');

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
				$is_public = $data->has('is_public')
					? ($data->is_public === true)
					: ($existing !== null);
				$is_link_required = $data->has('is_link_required')
					? ($data->is_link_required === true)
					: ($existing?->is_link_required === true);
				$grants_full_photo_access = $data->has('grants_full_photo_access')
					? ($data->grants_full_photo_access === true)
					: ($existing?->grants_full_photo_access === true);
				$grants_download = $data->has('grants_download')
					? ($data->grants_download === true)
					: ($existing?->grants_download === true);
				$grants_upload = $data->has('grants_upload')
					? ($data->grants_upload === true)
					: ($existing?->grants_upload === true);

				// is_nsfw may have been updated in group 1 via mass-update;
				// use the payload value if present, else the model value.
				$is_nsfw = $data->has('is_nsfw')
					? ($data->is_nsfw === true)
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
