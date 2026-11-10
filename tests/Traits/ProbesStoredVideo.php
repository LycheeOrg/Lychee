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

namespace Tests\Traits;

use App\Enum\StorageDiskType;
use App\Repositories\ConfigManager;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Facades\Storage;

/**
 * Reads the first video stream of a file stored on the local images disk,
 * with the ffprobe binary configured for Lychee (Feature 100, Q-100-09).
 */
trait ProbesStoredVideo
{
	protected function probeStoredVideo(string $short_path): Stream
	{
		$ffprobe = FFProbe::create([
			'ffprobe.binaries' => resolve(ConfigManager::class)->getValueAsString('ffprobe_path'),
		]);

		return $ffprobe->streams(Storage::disk(StorageDiskType::LOCAL->value)->path($short_path))->videos()->first();
	}
}
