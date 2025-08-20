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

namespace Tests\Feature_v2\RenamerRules;

use App\Enum\RenamerModeType;
use App\Models\RenamerRule;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenamerRulesUpdateTest extends BaseApiWithDataTest
{
	private RenamerRule $testRule;
	private RenamerRule $otherUserRule;

	public function setUp(): void
	{
		parent::setUp();

		// Create a test rule for userMayUpload1
		$this->testRule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'original_rule',
			'description' => 'Original description',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		// Create a rule for another user
		$this->otherUserRule = RenamerRule::create([
			'owner_id' => $this->userMayUpload2->id,
			'rule' => 'other_user_rule',
			'description' => 'Other user rule',
			'needle' => 'test',
			'replacement' => 'replaced',
			'mode' => RenamerModeType::FIRST,
			'order' => 1,
			'is_enabled' => false,
		]);
	}

	public function testUpdateRenamerRuleUnauthorized(): void
	{
		$response = $this->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'updated_rule',
			'description' => 'Updated description',
			'needle' => 'updated',
			'replacement' => 'changed',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertUnauthorized($response);
	}

	public function testUpdateRenamerRuleForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'updated_rule',
			'description' => 'Updated description',
			'needle' => 'updated',
			'replacement' => 'changed',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateRenamerRuleNotOwner(): void
	{
		// Try to update another user's rule
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->otherUserRule->id,
			'rule' => 'hacked_rule',
			'description' => 'Hacked description',
			'needle' => 'hack',
			'replacement' => 'owned',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testUpdateRenamerRuleUnprocessable(): void
	{
		// Test without required fields
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', []);
		$this->assertUnprocessable($response);

		// Test with invalid rule_id
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => 999999,
			'rule' => 'updated_rule',
			'description' => 'Updated description',
			'needle' => 'updated',
			'replacement' => 'changed',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertNotFound($response);

		// Test with invalid mode
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'updated_rule',
			'description' => 'Updated description',
			'needle' => 'updated',
			'replacement' => 'changed',
			'mode' => 'invalid_mode',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertUnprocessable($response);
	}

	public function testUpdateRenamerRuleAuthorized(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'updated_rule',
			'description' => 'Updated description',
			'needle' => 'updated',
			'replacement' => 'changed',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertOk($response);

		$response->assertJsonPath('id', $this->testRule->id);
		$response->assertJsonPath('rule', 'updated_rule');
		$response->assertJsonPath('description', 'Updated description');
		$response->assertJsonPath('needle', 'updated');
		$response->assertJsonPath('replacement', 'changed');
		$response->assertJsonPath('mode', 'first');
		$response->assertJsonPath('order', 2);
		$response->assertJsonPath('is_enabled', false);
		$response->assertJsonPath('owner_id', $this->userMayUpload1->id);
	}

	public function testUpdateRenamerRuleChangeModes(): void
	{
		// Update to REGEX mode
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'regex_rule',
			'description' => 'Regex rule',
			'needle' => '\d{4}-\d{2}-\d{2}',
			'replacement' => 'DATE',
			'mode' => 'regex',
			'order' => 3,
			'is_enabled' => true,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('mode', 'regex');
		$response->assertJsonPath('needle', '\d{4}-\d{2}-\d{2}');

		// Update to FIRST mode
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'first_rule',
			'description' => 'First rule',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => 'first',
			'order' => 1,
			'is_enabled' => false,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('mode', 'first');
		$response->assertJsonPath('needle', 'IMG_');
	}

	public function testUpdateRenamerRuleWithNullDescription(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'no_desc_rule',
			'description' => null,
			'needle' => 'test',
			'replacement' => 'tested',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('description', '');
	}

	public function testUpdateRenamerRuleStoresInDatabase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $this->testRule->id,
			'rule' => 'db_updated_rule',
			'description' => 'Database updated rule',
			'needle' => 'db_test',
			'replacement' => 'db_updated',
			'mode' => 'regex',
			'order' => 10,
			'is_enabled' => false,
		]);
		$this->assertOk($response);

		$this->assertDatabaseHas('renamer_rules', [
			'id' => $this->testRule->id,
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'db_updated_rule',
			'description' => 'Database updated rule',
			'needle' => 'db_test',
			'replacement' => 'db_updated',
			'mode' => RenamerModeType::REGEX->value,
			'order' => 10,
			'is_enabled' => false,
		]);
	}

	public function testUpdateRenamerRuleAsOwner(): void
	{
		// Admin should be able to update their own rules
		$adminRule = RenamerRule::create([
			'owner_id' => $this->admin->id,
			'rule' => 'admin_rule',
			'description' => 'Admin rule',
			'needle' => 'admin',
			'replacement' => 'ADMIN',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		$response = $this->actingAs($this->admin)->putJson('Renamer', [
			'rule_id' => $adminRule->id,
			'rule' => 'updated_admin_rule',
			'description' => 'Updated admin rule',
			'needle' => 'updated_admin',
			'replacement' => 'UPDATED_ADMIN',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('rule', 'updated_admin_rule');
		$response->assertJsonPath('owner_id', $this->admin->id);
	}
}
