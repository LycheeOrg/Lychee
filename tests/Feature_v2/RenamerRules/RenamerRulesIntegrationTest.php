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

use Illuminate\Http\Response;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenamerRulesIntegrationTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testCompleteRenamerRuleCrudWorkflow(): void
	{
		// 1. Start with empty list
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(0);

		// 2. Create first rule
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'first_rule',
			'description' => 'First test rule',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => 'first',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$firstRuleId = $response->json('id');

		// 3. Create second rule
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'second_rule',
			'description' => 'Second test rule',
			'needle' => '.jpg',
			'replacement' => '.jpeg',
			'mode' => 'all',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$secondRuleId = $response->json('id');

		// 4. List rules and verify order
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(2);
		$response->assertJsonPath('0.id', $firstRuleId);
		$response->assertJsonPath('0.order', 1);
		$response->assertJsonPath('1.id', $secondRuleId);
		$response->assertJsonPath('1.order', 2);

		// 5. Update first rule
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $firstRuleId,
			'rule' => 'updated_first_rule',
			'description' => 'Updated first rule',
			'needle' => 'DSC_',
			'replacement' => 'Camera_',
			'mode' => 'regex',
			'order' => 3,
			'is_enabled' => false,
		]);
		$this->assertOk($response);
		$response->assertJsonPath('rule', 'updated_first_rule');
		$response->assertJsonPath('mode', 'regex');
		$response->assertJsonPath('order', 3);

		// 6. List rules again to verify update and new order
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(2);
		// Now second rule should be first (order 1) and updated first rule should be second (order 3)
		$response->assertJsonPath('0.id', $secondRuleId);
		$response->assertJsonPath('0.order', 2);
		$response->assertJsonPath('1.id', $firstRuleId);
		$response->assertJsonPath('1.order', 3);
		$response->assertJsonPath('1.rule', 'updated_first_rule');

		// 7. Delete second rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $secondRuleId,
		]);
		$this->assertNoContent($response);

		// 8. Verify only first rule remains
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(1);
		$response->assertJsonPath('0.id', $firstRuleId);
		$response->assertJsonPath('0.rule', 'updated_first_rule');

		// 9. Delete remaining rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $firstRuleId,
		]);
		$this->assertNoContent($response);

		// 10. Verify empty list again
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(0);
	}

	public function testMultiUserRenamerRuleIsolation(): void
	{
		// Create rules for user1
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'user1_rule1',
			'description' => 'User 1 Rule 1',
			'needle' => 'user1',
			'replacement' => 'USER1',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$user1Rule1Id = $response->json('id');

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'user1_rule2',
			'description' => 'User 1 Rule 2',
			'needle' => 'test',
			'replacement' => 'TEST',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$user1Rule2Id = $response->json('id');

		// Create rules for user2
		$response = $this->actingAs($this->userMayUpload2)->postJson('Renamer', [
			'rule' => 'user2_rule1',
			'description' => 'User 2 Rule 1',
			'needle' => 'user2',
			'replacement' => 'USER2',
			'mode' => 'regex',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$user2Rule1Id = $response->json('id');

		// Verify user1 only sees their rules
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(2);
		$userRuleIds = collect($response->json())->pluck('id')->toArray();
		$this->assertContains($user1Rule1Id, $userRuleIds);
		$this->assertContains($user1Rule2Id, $userRuleIds);
		$this->assertNotContains($user2Rule1Id, $userRuleIds);

		// Verify user2 only sees their rules
		$response = $this->actingAs($this->userMayUpload2)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(1);
		$response->assertJsonPath('0.id', $user2Rule1Id);

		// Verify user1 cannot update user2's rule
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $user2Rule1Id,
			'rule' => 'hacked_rule',
			'description' => 'Hacked',
			'needle' => 'hack',
			'replacement' => 'owned',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertForbidden($response);

		// Verify user1 cannot delete user2's rule
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Renamer', [
			'rule_id' => $user2Rule1Id,
		]);
		$this->assertForbidden($response);

		// Verify admin can see all rules with all=true
		$response = $this->actingAs($this->admin)->getJsonWithData('Renamer', ['all' => true]);
		$this->assertOk($response);
		$response->assertJsonCount(3);
		$allRuleIds = collect($response->json())->pluck('id')->toArray();
		$this->assertContains($user1Rule1Id, $allRuleIds);
		$this->assertContains($user1Rule2Id, $allRuleIds);
		$this->assertContains($user2Rule1Id, $allRuleIds);
	}

	public function testRenamerRuleOrderingAndModeTypes(): void
	{
		// Create rules with different orders and modes
		$regexRule = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'regex_rule',
			'description' => 'Regex rule',
			'needle' => '\d{4}-\d{2}-\d{2}',
			'replacement' => 'DATE',
			'mode' => 'regex',
			'order' => 3,
			'is_enabled' => true,
		]);
		$this->assertStatus($regexRule, Response::HTTP_CREATED);

		$firstRule = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'first_rule',
			'description' => 'First mode rule',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => 'first',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($firstRule, Response::HTTP_CREATED);

		$allRule = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'all_rule',
			'description' => 'All mode rule',
			'needle' => ' ',
			'replacement' => '_',
			'mode' => 'all',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertStatus($allRule, Response::HTTP_CREATED);

		// Verify ordering (should be ordered by 'order' field ASC)
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(3);

		// Should be ordered: first_rule (order 1), all_rule (order 2), regex_rule (order 3)
		$response->assertJsonPath('0.rule', 'first_rule');
		$response->assertJsonPath('0.mode', 'first');
		$response->assertJsonPath('0.order', 1);

		$response->assertJsonPath('1.rule', 'all_rule');
		$response->assertJsonPath('1.mode', 'all');
		$response->assertJsonPath('1.order', 2);

		$response->assertJsonPath('2.rule', 'regex_rule');
		$response->assertJsonPath('2.mode', 'regex');
		$response->assertJsonPath('2.order', 3);
	}

	public function testRenamerRuleValidationEdgeCases(): void
	{
		// Test very long strings (within limits)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => str_repeat('a', 100), // Max length for rule
			'description' => str_repeat('b', 1000), // Max length for description
			'needle' => str_repeat('c', 255), // Max length for needle
			'replacement' => str_repeat('d', 255), // Max length for replacement
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);

		// Test minimum valid order
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'min_order_rule',
			'description' => 'Minimum order rule',
			'needle' => 'min',
			'replacement' => 'minimum',
			'mode' => 'first',
			'order' => 1,
			'is_enabled' => false,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);

		// Test all enum modes
		foreach (['first', 'all', 'regex'] as $mode) {
			$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
				'rule' => "test_mode_{$mode}",
				'description' => "Test mode {$mode}",
				'needle' => $mode,
				'replacement' => strtoupper($mode),
				'mode' => $mode,
				'order' => rand(1, 100),
				'is_enabled' => rand(0, 1) === 1,
			]);
			$this->assertStatus($response, Response::HTTP_CREATED);
			$response->assertJsonPath('mode', $mode);
		}
	}
}
