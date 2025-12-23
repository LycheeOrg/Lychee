<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Constants\PhotoAlbum as PA;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = SmartAlbumType::UNSORTED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	public function __construct(
		ConfigManager $config_manager,
	) {
		parent::__construct(
			config_manager: $config_manager,
			id: SmartAlbumType::UNSORTED,
			smart_condition: fn (Builder $q) => $q->whereNull(PA::ALBUM_ID)
		);
	}

	public static function getInstance(ConfigManager $config_manager): self
	{
		return self::$instance ??= new self($config_manager);
	}

	/**
	 * In the case of unsorted, we cannot determine whether the photo is visible or not from its parent.
	 * If the Unsorted album is made public, then all the pictures in it are visible (including pictures which are not owned by the current user).
	 *
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 */
	public function photos(): Builder
	{
		if ($this->public_permissions !== null) {
			return Photo::query()->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)->with(['size_variants', 'statistics', 'palette', 'tags'])
			->where($this->smart_photo_condition);
		}

		return parent::photos();
	}
}
