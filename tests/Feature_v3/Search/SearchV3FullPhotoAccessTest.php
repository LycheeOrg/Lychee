<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Search;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 069 / Q-069-12 — proves the v3 search `details` tier decides
 * full-resolution access from the **real per-album grant**, never from the
 * `grants_full_photo_access` *config*.
 *
 * That config is the seed value `AccessPermission::ofPublic()` stamps onto a
 * **newly created** share (`configs` default `1`); the authoritative value is
 * the `access_permissions.grants_full_photo_access` column (default `0`). v2's
 * search reads the former as a runtime gate and therefore over-grants. The two
 * decisive cases below are the ones where those two inputs disagree — if v3
 * ever regressed to reading the config, exactly those two would flip.
 *
 * Exercised over HTTP so the full middleware/policy stack participates.
 */
class SearchV3FullPhotoAccessTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		Config::set('features.struct-of-array', true);
	}

	/**
	 * @param string[] $photo_ids
	 */
	private function originalUrlFor(string $photo_id, array $photo_ids = []): ?string
	{
		$response = $this->getJsonV3('Search/Photos/details', [
			'terms' => base64_encode('CR_'),
			'photo_ids' => $photo_ids === [] ? [$photo_id] : $photo_ids,
		]);
		$this->assertOk($response);

		$ids = $response->json('ids');
		$index = array_search($photo_id, $ids, true);
		self::assertNotFalse($index, "photo {$photo_id} was not returned at all");

		// Guard against a vacuous pass: the downgrade only engages when a
		// medium variant exists, so assert one does before trusting a null.
		self::assertNotNull($response->json("size_variants.{$index}.medium"), 'fixture photo must have a medium variant');

		return $response->json("size_variants.{$index}.original.url");
	}

	private function setAlbumGrant(bool $granted): void
	{
		$this->perm44->grants_full_photo_access = $granted;
		$this->perm44->save();
	}

	// ── The two decisive cases: config and grant disagree ───────────

	/**
	 * The v2 bug, asserted absent. Global config ON, album grant OFF, viewer is
	 * not the owner: v2 hands out the original URL, v3 must not.
	 */
	public function testConfigOnButAlbumGrantOffIsDeniedForANonOwner(): void
	{
		Configs::set('grants_full_photo_access', '1');
		Configs::set('search_public', '1');
		$this->setAlbumGrant(false);

		self::assertNull($this->originalUrlFor($this->subPhoto4->id));
	}

	/**
	 * The mirror image: global config OFF, album grant ON. The grant wins.
	 */
	public function testConfigOffButAlbumGrantOnIsAllowedForANonOwner(): void
	{
		Configs::set('grants_full_photo_access', '0');
		Configs::set('search_public', '1');
		$this->setAlbumGrant(true);

		self::assertNotNull($this->originalUrlFor($this->subPhoto4->id));
	}

	// ── Ownership and admin short-circuits ──────────────────────────

	public function testOwnerAlwaysGetsFullAccessEvenWithTheConfigOff(): void
	{
		Configs::set('grants_full_photo_access', '0');
		$this->actingAs($this->userMayUpload1);

		// photo1 lives in album1, which grants userMayUpload1 nothing
		// explicitly — ownership alone must be sufficient.
		self::assertNotNull($this->originalUrlFor($this->photo1->id));
	}

	public function testAdminAlwaysGetsFullAccessEvenWithTheConfigOff(): void
	{
		Configs::set('grants_full_photo_access', '0');
		$this->setAlbumGrant(false);
		$this->actingAs($this->admin);

		self::assertNotNull($this->originalUrlFor($this->subPhoto4->id));
	}

	// ── The decision is per photo, not per response ─────────────────

	/**
	 * The property a request-wide boolean structurally cannot express: two
	 * photos in one response with different answers.
	 */
	public function testTwoPhotosInOneResponseGetIndependentAnswers(): void
	{
		Configs::set('grants_full_photo_access', '1');
		$this->setAlbumGrant(false);
		$this->actingAs($this->userMayUpload1);

		$ids = [$this->photo1->id, $this->subPhoto4->id];

		// Owned by the caller -> allowed.
		self::assertNotNull($this->originalUrlFor($this->photo1->id, $ids));
		// Someone else's, album grant withheld -> denied, in the same response.
		self::assertNull($this->originalUrlFor($this->subPhoto4->id, $ids));
	}
}
