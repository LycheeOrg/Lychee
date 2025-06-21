<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Constants;

use App\Constants\PhotoAlbum;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Tests\AbstractTestCase;

class PhotoAlbumTest extends AbstractTestCase
{
	public function testPhotoAlbumFalse(): void
	{
		$join_clause = new JoinClause(resolve(Builder::class), 'left', 'photos');
		self::assertFalse(PhotoAlbum::isJoinedToPhoto($join_clause));

		$join_clause = new JoinClause(resolve(Builder::class), 'left', PhotoAlbum::PHOTO_ALBUM);
		self::assertFalse(PhotoAlbum::isJoinedToPhoto($join_clause));
	}
}
