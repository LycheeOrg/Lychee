<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * Regression test: the face-cluster endpoints (GET /FaceDetection/clusters and
 * GET /FaceDetection/clusters/{label}/faces) must not disclose face-crop
 * preview URLs or photo_id references for photos inside a password-protected
 * album that the requesting user has never unlocked.
 *
 * This is the same asymmetric-access bug class as CVE-2026-61838, which hit
 * /Zip: one code path enforces the album password while a sibling path
 * doesn't. `5dcb0bf1` ("Add missing check on face displays") added the
 * accessibility filter these endpoints rely on, but the only regression
 * tests it shipped with (FaceClusterFacesTest, FaceClusterReviewTest) cover a
 * plain *private* album, not a *password-protected* one. This file closes
 * that gap.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\AssistedVision\Face;

use App\Models\Configs;
use App\Models\Face;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FaceClusterPasswordProtectedAlbumTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_person_is_searchable_default', '1');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	/**
	 * Turns album4 (public, visible, owned by userLocked; photo4 lives inside
	 * it) into a password-protected album, mirroring
	 * PasswordDownloadBypassTest::makePublicPasswordDownloadableAlbum().
	 */
	private function protectAlbum4WithPassword(): void
	{
		$this->perm4->is_link_required = false;
		$this->perm4->user_id = null;
		$this->perm4->user_group_id = null;
		$this->perm4->password = Hash::make('the-secret');
		$this->perm4->save();
		$this->album4->refresh();
	}

	// ── LEAK: cluster listing (preview crop URLs) ─────────────────

	public function testListClustersExcludesFacesFromPasswordProtectedAlbum(): void
	{
		$this->protectAlbum4WithPassword();
		Face::factory()->for_photo($this->photo4)->with_cluster(200)->count(3)->create();

		// Attacker: a logged-in, unrelated user (userNoUpload owns unrelated
		// album3) who has never supplied the password / unlocked album4.
		$response = $this->actingAs($this->userNoUpload)->getJson('FaceDetection/clusters');
		$this->assertOk($response);

		$cluster_labels = collect($response->json('data'))->pluck('cluster_label')->all();
		self::assertNotContains(200, $cluster_labels,
			'LEAK: face-cluster preview crops from a password-protected album were returned to a user who never unlocked it.');
	}

	public function testListClustersExcludesFacesFromPasswordProtectedAlbumInPrivateMode(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'private');
		$this->protectAlbum4WithPassword();
		Face::factory()->for_photo($this->photo4)->with_cluster(201)->count(3)->create();

		$response = $this->actingAs($this->userNoUpload)->getJson('FaceDetection/clusters');
		$this->assertOk($response);

		$cluster_labels = collect($response->json('data'))->pluck('cluster_label')->all();
		self::assertNotContains(201, $cluster_labels,
			'LEAK: face-cluster preview crops from a password-protected album were returned in PRIVATE face mode to a user who never unlocked it.');
	}

	// ── LEAK: cluster faces (photo_id disclosure) ─────────────────

	public function testGetFacesReturns404ForPasswordProtectedAlbum(): void
	{
		$this->protectAlbum4WithPassword();
		Face::factory()->for_photo($this->photo4)->with_cluster(210)->count(3)->create();

		$response = $this->actingAs($this->userNoUpload)->getJson('FaceDetection/clusters/210/faces');
		$this->assertNotFound($response);
	}

	// ── SANITY: filter is unlock-aware, not a blanket block ────────

	/**
	 * Proves the two tests above are meaningful: the same cluster becomes
	 * visible once the attacker's session actually unlocks album4, so the
	 * prior 404/exclusion is specifically due to the missing password, not
	 * some unrelated blanket restriction.
	 */
	public function testUnlockingAlbumRestoresVisibilityOfItsFaceCluster(): void
	{
		$this->protectAlbum4WithPassword();
		Face::factory()->for_photo($this->photo4)->with_cluster(220)->count(3)->create();

		$locked = $this->actingAs($this->userNoUpload)->getJson('FaceDetection/clusters');
		$this->assertOk($locked);
		self::assertNotContains(220, collect($locked->json('data'))->pluck('cluster_label')->all());

		// Simulate the user having supplied the correct password.
		session()->push(AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY, $this->album4->id);

		$unlocked = $this->actingAs($this->userNoUpload)->getJson('FaceDetection/clusters');
		$this->assertOk($unlocked);
		self::assertContains(220, collect($unlocked->json('data'))->pluck('cluster_label')->all());
	}

	// ── CONTROL: browsing and /Zip correctly require the password ─

	/**
	 * Control confirming the leak surface is specific to the face-cluster
	 * path: ordinary album browsing and archive download both correctly deny
	 * the attacker (post-CVE-2026-61838).
	 */
	public function testControlAlbumBrowsingAndZipDenyAccessToPasswordProtectedAlbum(): void
	{
		$this->protectAlbum4WithPassword();

		$head = $this->actingAs($this->userNoUpload)->getJsonWithData('Album::head', ['album_id' => $this->album4->id]);
		self::assertContains($head->getStatusCode(), [401, 403], 'Expected album browsing to be blocked by the password, got ' . $head->getStatusCode());

		$photos = $this->actingAs($this->userNoUpload)->getJsonWithData('Album::photos', ['album_id' => $this->album4->id]);
		self::assertContains($photos->getStatusCode(), [401, 403], 'Expected photo listing to be blocked by the password, got ' . $photos->getStatusCode());

		$zip = $this->actingAs($this->userNoUpload)->getWithParameters('/api/v2/Zip', ['album_ids' => $this->album4->id], ['Accept' => '*/*']);
		if ($zip->baseResponse instanceof StreamedResponse) {
			$zip->streamedContent();
		}
		self::assertContains($zip->getStatusCode(), [401, 403], 'Expected /Zip to be blocked by the password, got ' . $zip->getStatusCode());
	}
}
