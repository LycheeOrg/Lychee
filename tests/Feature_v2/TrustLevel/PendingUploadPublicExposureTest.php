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

namespace Tests\Feature_v2\TrustLevel;

use App\Enum\DownloadVariantType;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Regression tests for GHSA-mf2c-48xf-pwwm.
 *
 * A photo which is hidden from the ordinary gallery listing because it is
 * pending moderation (`is_validated = false`) must also stay hidden from the
 * two other public resolvers that can independently expose it: the ZIP
 * download endpoint and the landing page's `random_from_album` background.
 */
class PendingUploadPublicExposureTest extends BaseApiWithDataTest
{
	public function testZipDownloadRejectsPendingPhoto(): void
	{
		$this->perm4->grants_download = true;
		$this->perm4->save();

		$this->photo4->is_validated = false;
		$this->photo4->save();

		try {
			// Sanity check: the pending photo is hidden from the ordinary listing.
			$response = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
			$this->assertOk($response);
			$ids = collect($response->json('photos'))->pluck('id')->toArray();
			$this->assertNotContains($this->photo4->id, $ids, 'Pending photo should not appear in the ordinary listing');

			// The ZIP endpoint must not allow an anonymous, cookie-free download either.
			// Guests who fail authorization get 401 (vs. 403 for a rejected authenticated user).
			$this->download(
				photo_ids: [$this->photo4->id],
				from_id: $this->album4->id,
				kind: DownloadVariantType::ORIGINAL,
				expectedStatusCode: 401,
			);
		} finally {
			$this->photo4->is_validated = true;
			$this->photo4->save();
			$this->perm4->grants_download = false;
			$this->perm4->save();
		}
	}

	public function testLandingPageRandomFromAlbumHidesPendingPhoto(): void
	{
		$originalMode = Configs::query()->where('key', '=', 'landing_background_landscape_mode')->value('value');
		$originalValue = Configs::query()->where('key', '=', 'landing_background_landscape')->value('value');

		$this->photo4->is_validated = false;
		$this->photo4->save();

		// album4 only contains photo4, so a non-fallback result can only be that pending photo.
		Configs::set('landing_background_landscape_mode', 'random_from_album');
		Configs::set('landing_background_landscape', $this->album4->id);

		try {
			$response = $this->getJson('LandingPage');
			$this->assertOk($response);
			$response->assertJsonPath('landing_background_landscape', 'dist/cat.webp');
		} finally {
			$this->photo4->is_validated = true;
			$this->photo4->save();
			if ($originalMode !== null) {
				Configs::set('landing_background_landscape_mode', $originalMode);
			}
			if ($originalValue !== null) {
				Configs::set('landing_background_landscape', $originalValue);
			}
		}
	}
}
