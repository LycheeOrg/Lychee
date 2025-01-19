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

namespace Tests\Feature_v1;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Constants\TestConstants;
use Tests\Feature_v1\Base\BasePhotoTest;

class CommandTakeDateTest extends BasePhotoTest
{
	public const COMMAND = 'lychee:takedate';

	public function testNoUpdateRequired(): void
	{
		$this->artisan(self::COMMAND)
			->expectsOutput('No pictures require takedate updates.')
			->assertExitCode(-1);
	}

	public function testSetUploadTimeFromFileTime(): void
	{
		$id = $this->photos_tests->upload(
			static::createUploadedFile(TestConstants::SAMPLE_FILE_MONGOLIA_IMAGE)
		)->offsetGet('id');

		DB::table('photos')
			->where('id', '=', $id)
			->update(['created_at' => Carbon::createFromDate(1970, 01, 01)->format('Y-m-d H:i:s.u')]);

		$this->artisan(self::COMMAND, [
			'--set-upload-time' => true,
			'--force' => true,
		])
			->assertSuccessful();

		/** @var \App\Models\Photo */
		$photo = static::convertJsonToObject($this->photos_tests->get($id));

		$file_time = \Safe\filemtime(public_path($this->dropUrlPrefix($photo->size_variants->original->url)));
		$carbon = new Carbon($photo->created_at);

		self::assertEquals($file_time, $carbon->getTimestamp());
	}
}
