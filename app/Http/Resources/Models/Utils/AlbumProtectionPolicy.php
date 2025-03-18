<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Models\Utils;

use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * This represents the Album Protection Policy.
 *
 * In other words it provides information on the security attributes of an album.
 * It MUST NOT be used in the front-end to determine whether an action is permitted or not (e.g. share link available).
 *
 * It is used in two places:
 * 1. When sent to the front-end:
 *   - provides the policy of the currently visited album
 *   - allows modification of the policy on non-smart albums
 * 2. When received from the front-end:
 *   - allows for an easy interface between the validated request {@link \App\Http\Requests\Album\SetAlbumProtectionPolicyRequest}
 *     and the applied action {@link \App\Actions\Album\SetProtectionPolicy}.
 */
#[TypeScript()]
class AlbumProtectionPolicy extends Data
{
	public function __construct(
		public bool $is_public,
		public bool $is_link_required,
		public bool $is_nsfw,
		public bool $grants_full_photo_access,
		public bool $grants_download,
		public bool $grants_upload,
		public bool $is_password_required = false, // Only used when sending info to the front-end
	) {
	}

	/**
	 * Given a {@link BaseAlbum}, returns the Protection Policy associated to it.
	 *
	 * @param BaseAlbum $baseAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofBaseAlbum(BaseAlbum $base_album): self
	{
		return new self(
			is_public: $base_album->public_permissions() !== null,
			is_link_required: $base_album->public_permissions()?->is_link_required === true,
			is_nsfw: $base_album->is_nsfw,
			grants_full_photo_access: $base_album->public_permissions()?->grants_full_photo_access === true,
			grants_download: $base_album->public_permissions()?->grants_download === true,
			grants_upload: $base_album->public_permissions()?->grants_upload === true,
			is_password_required: $base_album->public_permissions()?->password !== null,
		);
	}

	/**
	 * Given a smart album, returns the Protection Policy associated to it.
	 *
	 * @param BaseSmartAlbum $baseSmartAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofSmartAlbum(BaseSmartAlbum $base_smart_album): self
	{
		return new self(
			is_public: $base_smart_album->public_permissions() !== null,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: $base_smart_album->public_permissions()?->grants_full_photo_access === true,
			grants_download: $base_smart_album->public_permissions()?->grants_download === true,
			grants_upload: false,
			is_password_required: false,
		);
	}
}
