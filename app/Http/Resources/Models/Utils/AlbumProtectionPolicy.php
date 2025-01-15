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
	public static function ofBaseAlbum(BaseAlbum $baseAlbum): self
	{
		return new self(
			is_public: $baseAlbum->public_permissions() !== null,
			is_link_required: $baseAlbum->public_permissions()?->is_link_required === true,
			is_nsfw: $baseAlbum->is_nsfw,
			grants_full_photo_access: $baseAlbum->public_permissions()?->grants_full_photo_access === true,
			grants_download: $baseAlbum->public_permissions()?->grants_download === true,
			is_password_required: $baseAlbum->public_permissions()?->password !== null,
		);
	}

	/**
	 * Given a smart album, returns the Protection Policy associated to it.
	 *
	 * @param BaseSmartAlbum $baseSmartAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofSmartAlbum(BaseSmartAlbum $baseSmartAlbum): self
	{
		return new self(
			is_public: $baseSmartAlbum->public_permissions() !== null,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: $baseSmartAlbum->public_permissions()?->grants_full_photo_access === true,
			grants_download: $baseSmartAlbum->public_permissions()?->grants_download === true,
			is_password_required: false,
		);
	}
}
