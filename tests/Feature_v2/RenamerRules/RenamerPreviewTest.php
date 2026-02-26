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

namespace Tests\Feature_v2\RenamerRules;

use App\Enum\RenamerModeType;
use App\Models\RenamerRule;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenamerPreviewTest extends BaseApiWithDataTest
{
	private int $rule_id;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		\Configs::set('renamer_enabled', true);

		// Create a renamer rule that replaces all occurrences of the photo1 title prefix
		$rule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'preview_test_rule',
			'description' => 'Replace old with new for preview testing',
			'needle' => $this->photo1->title,
			'replacement' => 'Renamed_' . $this->photo1->title,
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => false,
		]);
		$this->rule_id = $rule->id;
	}

	public function tearDown(): void
	{
		\Configs::set('renamer_enabled', false);
		$this->resetSe();
		parent::tearDown();
	}

	public function testPreviewUnauthenticated(): void
	{
		$response = $this->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$this->rule_id],
		]);
		$this->assertUnauthorized($response);
	}

	public function testPreviewForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->postJson('Renamer::preview', [
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$this->rule_id],
			'photo_ids' => [$this->photo1->id],
		]);
		// userNoUpload cannot edit photo1 (owned by userMayUpload1)
		$this->assertForbidden($response);
	}

	public function testPreviewValidationMissingRuleIds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
		]);
		$this->assertUnprocessable($response);
	}

	public function testPreviewValidationEmptyRuleIds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [],
		]);
		$this->assertUnprocessable($response);
	}

	public function testPreviewWithChangesCurrentScope(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$this->rule_id],
		]);
		$this->assertOk($response);

		$data = $response->json();
		// photo1 title should match the needle, so it should appear in changes
		$changed_ids = array_column($data, 'id');
		$this->assertContains($this->photo1->id, $changed_ids);

		// Find the photo1 entry and verify the new title
		$photo1_change = collect($data)->firstWhere('id', $this->photo1->id);
		$this->assertSame($this->photo1->title, $photo1_change['original']);
		$this->assertSame('Renamed_' . $this->photo1->title, $photo1_change['new']);
	}

	public function testPreviewNoChanges(): void
	{
		// Create a rule that won't match any photo titles
		$no_match_rule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'no_match_rule',
			'description' => 'Will not match anything',
			'needle' => 'ZZZZZZZZZZZ_NONEXISTENT_NEEDLE',
			'replacement' => 'REPLACED',
			'mode' => RenamerModeType::ALL,
			'order' => 2,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => false,
		]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$no_match_rule->id],
		]);
		$this->assertOk($response);
		$response->assertJsonCount(0);
	}

	public function testPreviewDescendantsScope(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'descendants',
			'rule_ids' => [$this->rule_id],
		]);
		$this->assertOk($response);

		$data = $response->json();
		$changed_ids = array_column($data, 'id');

		// photo1 is in album1 directly — should appear
		$this->assertContains($this->photo1->id, $changed_ids);
	}

	public function testPreviewWithExplicitPhotoIds(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$this->rule_id],
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertOk($response);

		$data = $response->json();
		$changed_ids = array_column($data, 'id');
		$this->assertContains($this->photo1->id, $changed_ids);
	}

	public function testPreviewRuleIdsFiltering(): void
	{
		// Create a second rule that doesn't match
		$no_match_rule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'no_effect_rule',
			'description' => 'Will not match anything',
			'needle' => 'NONEXISTENT_STRING_12345',
			'replacement' => 'REPLACED',
			'mode' => RenamerModeType::ALL,
			'order' => 3,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => false,
		]);

		// Only use the no-match rule — should return 0 changes
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$no_match_rule->id],
		]);
		$this->assertOk($response);
		$response->assertJsonCount(0);

		// Now use the matching rule — should return changes
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'photos',
			'scope' => 'current',
			'rule_ids' => [$this->rule_id],
		]);
		$this->assertOk($response);

		$data = $response->json();
		$this->assertNotEmpty($data);
	}

	public function testPreviewAlbumsTarget(): void
	{
		// Create an album renamer rule
		$album_rule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'album_rename_rule',
			'description' => 'Rename album titles',
			'needle' => $this->subAlbum1->title,
			'replacement' => 'Renamed_' . $this->subAlbum1->title,
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => false,
			'is_album_rule' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::preview', [
			'album_id' => $this->album1->id,
			'target' => 'albums',
			'scope' => 'current',
			'rule_ids' => [$album_rule->id],
		]);
		$this->assertOk($response);

		$data = $response->json();
		$changed_ids = array_column($data, 'id');
		$this->assertContains($this->subAlbum1->id, $changed_ids);
	}
}
