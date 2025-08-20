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

class RenamerRulesListTest extends BaseApiWithDataTest
{
	public function testListRenamerRulesUnauthorized(): void
	{
		$response = $this->getJson('Renamer');
		$this->assertUnauthorized($response);
	}

	public function testListRenamerRulesForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->getJson('Renamer');
		$this->assertForbidden($response);
	}

	public function testListRenamerRulesAuthorizedEmpty(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(0);
	}

	public function testListRenamerRulesAuthorizedWithRules(): void
	{
		// Create some renamer rules for userMayUpload1
		$rule1 = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'test_rule_1',
			'description' => 'Test rule 1',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		$rule2 = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'test_rule_2',
			'description' => 'Test rule 2',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::FIRST,
			'order' => 2,
			'is_enabled' => false,
		]);

		// Create a rule for another user (should not be returned)
		RenamerRule::create([
			'owner_id' => $this->userMayUpload2->id,
			'rule' => 'other_user_rule',
			'description' => 'Other user rule',
			'needle' => 'test',
			'replacement' => 'replaced',
			'mode' => RenamerModeType::REGEX,
			'order' => 1,
			'is_enabled' => true,
		]);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(2);

		// Check the first rule (ordered by order ASC)
		$response->assertJsonPath('0.id', $rule1->id);
		$response->assertJsonPath('0.rule', 'test_rule_1');
		$response->assertJsonPath('0.description', 'Test rule 1');
		$response->assertJsonPath('0.needle', 'old');
		$response->assertJsonPath('0.replacement', 'new');
		$response->assertJsonPath('0.mode', 'all');
		$response->assertJsonPath('0.order', 1);
		$response->assertJsonPath('0.is_enabled', true);
		$response->assertJsonPath('0.owner_id', $this->userMayUpload1->id);

		// Check the second rule
		$response->assertJsonPath('1.id', $rule2->id);
		$response->assertJsonPath('1.rule', 'test_rule_2');
		$response->assertJsonPath('1.description', 'Test rule 2');
		$response->assertJsonPath('1.needle', 'IMG_');
		$response->assertJsonPath('1.replacement', 'Photo_');
		$response->assertJsonPath('1.mode', 'first');
		$response->assertJsonPath('1.order', 2);
		$response->assertJsonPath('1.is_enabled', false);
		$response->assertJsonPath('1.owner_id', $this->userMayUpload1->id);
	}

	public function testListAllRenamerRulesAsAdmin(): void
	{
		// Create rules for different users
		$rule1 = RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'user1_rule',
			'description' => 'User 1 rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		$rule2 = RenamerRule::create([
			'owner_id' => $this->userMayUpload2->id,
			'rule' => 'user2_rule',
			'description' => 'User 2 rule',
			'needle' => 'test',
			'replacement' => 'replaced',
			'mode' => RenamerModeType::REGEX,
			'order' => 1,
			'is_enabled' => true,
		]);

		// Test as admin without 'all' parameter (should return only admin's rules)
		$response = $this->actingAs($this->admin)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(0); // Admin has no rules

		// Test as admin with 'all=false' (should return only admin's rules)
		$response = $this->actingAs($this->admin)->getJsonWithData('Renamer', ['all' => false]);
		$this->assertOk($response);
		$response->assertJsonCount(0); // Admin has no rules

		// Test as admin with 'all=true' (should return all rules)
		$response = $this->actingAs($this->admin)->getJsonWithData('Renamer', ['all' => true]);
		$this->assertOk($response);
		$response->assertJsonCount(2);

		// Verify both rules are returned
		$ruleIds = collect($response->json())->pluck('id')->toArray();
		$this->assertContains($rule1->id, $ruleIds);
		$this->assertContains($rule2->id, $ruleIds);
	}

	public function testListAllRenamerRulesAsNonAdminIgnored(): void
	{
		// Create rules for different users
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'user1_rule',
			'description' => 'User 1 rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
		]);

		RenamerRule::create([
			'owner_id' => $this->userMayUpload2->id,
			'rule' => 'user2_rule',
			'description' => 'User 2 rule',
			'needle' => 'test',
			'replacement' => 'replaced',
			'mode' => RenamerModeType::REGEX,
			'order' => 1,
			'is_enabled' => true,
		]);

		// Test as non-admin with 'all=true' (should still return only own rules)
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Renamer', ['all' => true]);
		$this->assertOk($response);
		$response->assertJsonCount(1); // Only userMayUpload1's rule
		$response->assertJsonPath('0.rule', 'user1_rule');
	}
}
