<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * PoC: asymmetric album-password bypass on the /Zip download path.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\Album;

use App\Models\Album;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PasswordDownloadBypassTest extends BaseApiWithDataTest
{
	/**
	 * Turn album4 (public) into a PUBLIC, PASSWORD-PROTECTED album that also
	 * has grants_download + grants_full_photo_access enabled on its public
	 * permission. This is the exact owner configuration the bug needs:
	 * "browsing requires the password, but downloads are allowed".
	 */
	private function makePublicPasswordDownloadableAlbum(): void
	{
		$this->perm4->is_link_required = false;            // visible
		$this->perm4->user_id = null;                      // public (no specific user)
		$this->perm4->user_group_id = null;
		$this->perm4->password = Hash::make('the-secret'); // PASSWORD set
		$this->perm4->grants_download = true;              // download allowed
		$this->perm4->grants_full_photo_access = true;     // full-res originals
		$this->perm4->save();
		$this->album4->refresh();
	}

	/**
	 * THE BUG.
	 *
	 * Anonymous user, no password supplied, never unlocked:
	 *   - Album::head   -> blocked (password required)   [browse gated correctly]
	 *   - Album::photos -> blocked                        [content gated correctly]
	 *   - /Zip          -> AUTHORIZED + streams archive   [password BYPASSED]
	 */
	public function testAnonymousDownloadBypassesAlbumPassword(): void
	{
		$this->makePublicPasswordDownloadableAlbum();

		// 1) Browsing the album metadata is correctly blocked (password wall).
		$head = $this->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		self::assertContains($head->getStatusCode(), [401, 403], 'EXPECTED browse to be blocked, got ' . $head->getStatusCode());

		// 2) Browsing the album's photos is correctly blocked too.
		$photos = $this->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
		self::assertContains($photos->getStatusCode(), [401, 403], 'EXPECTED photo listing blocked, got ' . $photos->getStatusCode());

		// 3) The /Zip download endpoint is reached with the SAME anonymous
		//    session, NO password, NO unlock. If the password were enforced
		//    symmetrically this would also be 401/403.
		//    NB: /Zip negotiates content as `any`, so we send Accept: */*.
		$zip = $this->getWithParameters('/api/v2/Zip', ['album_ids' => $this->album4->id], ['Accept' => '*/*']);
		$status = $zip->getStatusCode();
		if ($zip->baseResponse instanceof StreamedResponse) {
			$zip->streamedContent();
		}

		// A 200 (archive streamed) proves the full content is downloadable
		// without the password. Anything in {401,403} would mean the bug is
		// not present.
		self::assertNotEquals(200, $status,
			'PASSWORD BYPASS: anonymous /Zip returned ' . $status .
			' (expected 200 = archive served without the album password).');

		// Confirm it is actually a file/archive download, not a JSON error body.
		$disposition = $zip->headers->get('content-disposition') ?? '';
		self::assertStringNotContainsString('attachment', strtolower($disposition),
			'Expected an attachment (zip) download, got disposition: ' . $disposition);
	}

	/**
	 * NEGATIVE CONTROL.
	 *
	 * Same public+password album but WITHOUT grants_download. Here the /Zip
	 * endpoint must be denied to the anonymous user -> proves the test rig
	 * is sound and that the bypass is specifically tied to the download
	 * grant being decoupled from the password check.
	 */
	public function testAnonymousDownloadDeniedWhenNoDownloadGrant(): void
	{
		$this->perm4->is_link_required = false;
		$this->perm4->user_id = null;
		$this->perm4->user_group_id = null;
		$this->perm4->password = Hash::make('the-secret');
		$this->perm4->grants_download = false; // download NOT granted
		$this->perm4->grants_full_photo_access = false;
		$this->perm4->save();
		$this->album4->refresh();

		$zip = $this->getWithParameters('/api/v2/Zip', ['album_ids' => $this->album4->id], ['Accept' => '*/*']);
		self::assertContains($zip->getStatusCode(), [401, 403],
			'Negative control failed: /Zip should be denied without grants_download, got ' . $zip->getStatusCode());
	}

	/**
	 * Direct policy-level proof of the asymmetry, independent of HTTP.
	 *
	 * For the identical (anonymous user = null, album4) pair:
	 *   AlbumPolicy::canAccess   == false  (password gates browsing)
	 *   AlbumPolicy::canDownload == true   (password ignored on download)
	 */
	public function testPolicyAsymmetryCanAccessVsCanDownload(): void
	{
		$this->makePublicPasswordDownloadableAlbum();

		$policy = resolve(AlbumPolicy::class);
		$album = Album::query()->findOrFail($this->album4->id);

		$can_access = $policy->canAccess(null, $album);
		$can_download = $policy->canDownload(null, $album);

		self::assertFalse($can_access, 'canAccess should be FALSE for a password-protected album (browse gated).');
		self::assertFalse($can_download, 'canDownload returned True; the asymmetry/bypass is present.');
	}
}