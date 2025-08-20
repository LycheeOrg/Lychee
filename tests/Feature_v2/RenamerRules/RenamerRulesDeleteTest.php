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

class RenamerRulesDeleteTest extends BaseApiWithDataTest
{
	private RenamerRule $testRule;
	private RenamerRule $otherUserRule;

	public function setUp(): void
	{
		parent::setUp();

		// Create a test rule for userMayUpload1
		$this->testRule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'test_rule',
			'description' => 'Test rule for deletion',
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

	public function testDeleteRenamerRuleUnauthorized(): void
	{
		$response = $this->deleteJson('Renamer', [
			'rule_id' => $this->testRule->id,
		]);
		$this->assertUnauthorized($response);
	}

	public function testDeleteRenamerRuleForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->deleteJson('Renamer', [
			'rule_id' => $this->testRule->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteRenamerRuleNotOwner(): void
	{
		// Try to delete another user's rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $this->otherUserRule->id,
		]);
		$this->assertForbidden($response);
	}

	public function testDeleteRenamerRuleUnprocessable(): void
	{
		// Test without required fields
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', []);
		$this->assertUnprocessable($response);

		// Test with invalid rule_id
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => 999999,
		]);
		$this->assertNotFound($response);

		// Test with non-integer rule_id
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => 'invalid',
		]);
		$this->assertUnprocessable($response);
	}

	public function testDeleteRenamerRuleAuthorized(): void
	{
		// Verify the rule exists
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $this->testRule->id,
		]);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $this->testRule->id,
		]);
		$this->assertNoContent($response);

		// Verify the rule is deleted
		$this->assertDatabaseMissing('renamer_rules', [
			'id' => $this->testRule->id,
		]);
	}

	public function testDeleteRenamerRuleAsOwner(): void
	{
		// Admin should be able to delete their own rules
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

		// Verify the rule exists
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $adminRule->id,
		]);

		$response = $this->actingAs($this->admin)->deleteJson('Renamer', [
			'rule_id' => $adminRule->id,
		]);
		$this->assertNoContent($response);

		// Verify the rule is deleted
		$this->assertDatabaseMissing('renamer_rules', [
			'id' => $adminRule->id,
		]);
	}

	public function testDeleteRenamerRuleDoesNotAffectOtherRules(): void
	{
		// Create another rule for the same user
		$anotherRule = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'another_rule',
			'description' => 'Another rule',
			'needle' => 'another',
			'replacement' => 'different',
			'mode' => RenamerModeType::FIRST,
			'order' => 2,
			'is_enabled' => false,
		]);

		// Delete the first rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $this->testRule->id,
		]);
		$this->assertNoContent($response);

		// Verify the first rule is deleted
		$this->assertDatabaseMissing('renamer_rules', [
			'id' => $this->testRule->id,
		]);

		// Verify the other rules still exist
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $anotherRule->id,
		]);
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $this->otherUserRule->id,
		]);
	}

	public function testDeleteMultipleRenamerRules(): void
	{
		// Create multiple rules for the user
		$rule1 = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'rule_1',
			'description' => 'Rule 1',
			'needle' => 'one',
			'replacement' => '1',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		$rule2 = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'rule_2',
			'description' => 'Rule 2',
			'needle' => 'two',
			'replacement' => '2',
			'mode' => RenamerModeType::FIRST,
			'order' => 2,
			'is_enabled' => true,
		]);

		// Delete first rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $rule1->id,
		]);
		$this->assertNoContent($response);

		// Delete second rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $rule2->id,
		]);
		$this->assertNoContent($response);

		// Verify both rules are deleted
		$this->assertDatabaseMissing('renamer_rules', [
			'id' => $rule1->id,
		]);
		$this->assertDatabaseMissing('renamer_rules', [
			'id' => $rule2->id,
		]);

		// Verify the original test rule still exists
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $this->testRule->id,
		]);
	}
}
