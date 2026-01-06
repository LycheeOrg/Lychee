<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UnsortedAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::UNSORTED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::UNSORTED,
			smart_condition: fn (Builder $q) => $q->whereNull(PA::ALBUM_ID)
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * In the case of unsorted, we cannot determine whether the photo is visible or not from its parent.
	 * If the Unsorted album is made public, then all the pictures in it are visible (including pictures which are not owned by the current user).
	 *
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 */
	public function photos(): Builder
	{
		$config_manager = resolve(ConfigManager::class);
		if ($this->public_permissions !== null && (!Auth::check() || !$config_manager->getValueAsBool('enable_smart_album_per_owner'))) {
			return Photo::query()->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)->with(['size_variants', 'statistics', 'palette', 'tags', 'rating'])
			->where($this->smart_photo_condition);
		}

		return parent::photos();
	}
}
