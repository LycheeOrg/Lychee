<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = SmartAlbumType::UNSORTED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		parent::__construct(
			SmartAlbumType::UNSORTED,
			fn (Builder $q) => $q->whereNull('photos.album_id')
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}

	/**
	 * In the case of unsorted, we cannot determine whether the photo is visible or not from its parent.
	 * If the Unsorted album is made public, then all the pictures in it are visible (including pictures which are not owned by the current user).
	 *
	 * @return \App\Eloquent\FixedQueryBuilder<Photo>
	 */
	public function photos(): Builder
	{
		if ($this->publicPermissions !== null) {
			return Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links'])
			->where($this->smartPhotoCondition);
		}

		return parent::photos();
	}
}
