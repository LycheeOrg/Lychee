<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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
use App\Events\Metrics\PhotoDownload;
use Tests\AbstractTestCase;

class PhotoDownloadTest extends AbstractTestCase
{
	/**
	 * Iterate over the directories and check if the files contain the correct license and copyright info..
	 *
	 * @return void
	 */
	public function testPhotoDownload(): void
	{
		$photo_download = new PhotoDownload('visitor-id', 'photo-id', 'album-id');
		self::assertEquals(MetricsAction::DOWNLOAD, $photo_download->metricAction());
	}
}