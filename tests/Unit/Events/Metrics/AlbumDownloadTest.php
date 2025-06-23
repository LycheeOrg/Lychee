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

namespace Tests\Unit\Events\Metrics;

use App\Enum\MetricsAction;
use App\Events\Metrics\AlbumDownload;
use Tests\AbstractTestCase;

class AlbumDownloadTest extends AbstractTestCase
{
	/**
	 * Iterate over the directories and check if the files contain the correct license and copyright info..
	 *
	 * @return void
	 */
	public function testAlbumDownload(): void
	{
		$album_download = new AlbumDownload('visitor-id', 'album-id');
		self::assertEquals(MetricsAction::DOWNLOAD, $album_download->metricAction());
	}
}