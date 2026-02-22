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
use Illuminate\Database\Eloquent\Builder;

class HighlightedAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::HIGHLIGHTED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::HIGHLIGHTED,
			smart_condition: fn (Builder $q) => $q->where('photos.is_highlighted', '=', true)
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}
}
