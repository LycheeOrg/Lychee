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

class RenamerRulesOrderTest extends BaseApiWithDataTest
{
	public function testUpdateRuleOrderMovingDown(): void
	{
		// Create 5 rules with orders 1, 2, 3, 4, 5
		$rules = [];
		for ($i = 1; $i <= 5; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move rule 2 to position 4 (moving down)
		// Expected result: rule_1(1), rule_3(2), rule_4(3), rule_2(4), rule_5(5)
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[2]->id,
			'rule' => 'rule_2',
			'description' => 'Rule 2',
			'needle' => 'needle_2',
			'replacement' => 'replacement_2',
			'mode' => 'all',
			'order' => 4,
			'is_enabled' => true,
		]);
		$this->assertOk($response);

		// Verify the new order
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[1]->id, 'order' => 1]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[2]->id, 'order' => 4]); // moved
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[3]->id, 'order' => 2]); // shifted up
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[4]->id, 'order' => 3]); // shifted up
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[5]->id, 'order' => 5]);
	}

	public function testUpdateRuleOrderMovingUp(): void
	{
		// Create 5 rules with orders 1, 2, 3, 4, 5
		$rules = [];
		for ($i = 1; $i <= 5; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move rule 4 to position 2 (moving up)
		// Expected result: rule_1(1), rule_4(2), rule_2(3), rule_3(4), rule_5(5)
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[4]->id,
			'rule' => 'rule_4',
			'description' => 'Rule 4',
			'needle' => 'needle_4',
			'replacement' => 'replacement_4',
			'mode' => 'all',
			'order' => 2,
			'is_enabled' => true,
		]);
		$this->assertOk($response);

		// Verify the new order
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[1]->id, 'order' => 1]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[2]->id, 'order' => 3]); // shifted down
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[3]->id, 'order' => 4]); // shifted down
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[4]->id, 'order' => 2]); // moved
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[5]->id, 'order' => 5]);
	}

	public function testUpdateRuleOrderSamePosition(): void
	{
		// Create 3 rules with orders 1, 2, 3
		$rules = [];
		for ($i = 1; $i <= 3; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Update rule 2 but keep same order
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[2]->id,
			'rule' => 'updated_rule_2',
			'description' => 'Updated Rule 2',
			'needle' => 'updated_needle_2',
			'replacement' => 'updated_replacement_2',
			'mode' => 'first',
			'order' => 2, // Same order
			'is_enabled' => false,
		]);
		$this->assertOk($response);

		// Verify orders haven't changed
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[1]->id, 'order' => 1]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[2]->id, 'order' => 2]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[3]->id, 'order' => 3]);

		// Verify other fields were updated
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $rules[2]->id,
			'rule' => 'updated_rule_2',
			'description' => 'Updated Rule 2',
			'needle' => 'updated_needle_2',
			'replacement' => 'updated_replacement_2',
			'mode' => 'first',
			'is_enabled' => false,
		]);
	}

	public function testUpdateRuleOrderToFirstPosition(): void
	{
		// Create 4 rules with orders 1, 2, 3, 4
		$rules = [];
		for ($i = 1; $i <= 4; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move rule 3 to position 1
		// Expected result: rule_3(1), rule_1(2), rule_2(3), rule_4(4)
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[3]->id,
			'rule' => 'rule_3',
			'description' => 'Rule 3',
			'needle' => 'needle_3',
			'replacement' => 'replacement_3',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertOk($response);

		// Verify the new order
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[1]->id, 'order' => 2]); // shifted down
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[2]->id, 'order' => 3]); // shifted down
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[3]->id, 'order' => 1]); // moved
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[4]->id, 'order' => 4]);
	}

	public function testUpdateRuleOrderToLastPosition(): void
	{
		// Create 4 rules with orders 1, 2, 3, 4
		$rules = [];
		for ($i = 1; $i <= 4; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move rule 2 to position 4
		// Expected result: rule_1(1), rule_3(2), rule_4(3), rule_2(4)
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[2]->id,
			'rule' => 'rule_2',
			'description' => 'Rule 2',
			'needle' => 'needle_2',
			'replacement' => 'replacement_2',
			'mode' => 'all',
			'order' => 4,
			'is_enabled' => true,
		]);
		$this->assertOk($response);

		// Verify the new order
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[1]->id, 'order' => 1]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[2]->id, 'order' => 4]); // moved
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[3]->id, 'order' => 2]); // shifted up
		$this->assertDatabaseHas('renamer_rules', ['id' => $rules[4]->id, 'order' => 3]); // shifted up
	}

	public function testUpdateRuleOrderDoesNotAffectOtherUsers(): void
	{
		// Create rules for user1
		$user1Rules = [];
		for ($i = 1; $i <= 3; $i++) {
			$user1Rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'user1_rule_' . $i,
				'description' => 'User 1 Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Create rules for user2 with same orders
		$user2Rules = [];
		for ($i = 1; $i <= 3; $i++) {
			$user2Rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload2->id,
				'rule' => 'user2_rule_' . $i,
				'description' => 'User 2 Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move user1's rule 1 to position 3
		$response = $this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $user1Rules[1]->id,
			'rule' => 'user1_rule_1',
			'description' => 'User 1 Rule 1',
			'needle' => 'needle_1',
			'replacement' => 'replacement_1',
			'mode' => 'all',
			'order' => 3,
			'is_enabled' => true,
		]);
		$this->assertOk($response);

		// Verify user1's rules were reordered
		$this->assertDatabaseHas('renamer_rules', ['id' => $user1Rules[1]->id, 'order' => 3]); // moved
		$this->assertDatabaseHas('renamer_rules', ['id' => $user1Rules[2]->id, 'order' => 1]); // shifted up
		$this->assertDatabaseHas('renamer_rules', ['id' => $user1Rules[3]->id, 'order' => 2]); // shifted up

		// Verify user2's rules were NOT affected
		$this->assertDatabaseHas('renamer_rules', ['id' => $user2Rules[1]->id, 'order' => 1]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $user2Rules[2]->id, 'order' => 2]);
		$this->assertDatabaseHas('renamer_rules', ['id' => $user2Rules[3]->id, 'order' => 3]);
	}

	public function testListRulesAfterOrderUpdate(): void
	{
		// Create 3 rules
		$rules = [];
		for ($i = 1; $i <= 3; $i++) {
			$rules[$i] = RenamerRule::create([
				'owner_id' => $this->userMayUpload1->id,
				'rule' => 'rule_' . $i,
				'description' => 'Rule ' . $i,
				'needle' => 'needle_' . $i,
				'replacement' => 'replacement_' . $i,
				'mode' => RenamerModeType::ALL,
				'order' => $i,
				'is_enabled' => true,
			]);
		}

		// Move rule 3 to position 1
		$this->actingAs($this->userMayUpload1)->putJson('Renamer', [
			'rule_id' => $rules[3]->id,
			'rule' => 'rule_3',
			'description' => 'Rule 3',
			'needle' => 'needle_3',
			'replacement' => 'replacement_3',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);

		// List rules and verify they're returned in the correct order
		$response = $this->actingAs($this->userMayUpload1)->getJson('Renamer');
		$this->assertOk($response);
		$response->assertJsonCount(3);

		// Should be ordered: rule_3(1), rule_1(2), rule_2(3)
		$response->assertJsonPath('0.rule', 'rule_3');
		$response->assertJsonPath('0.order', 1);

		$response->assertJsonPath('1.rule', 'rule_1');
		$response->assertJsonPath('1.order', 2);

		$response->assertJsonPath('2.rule', 'rule_2');
		$response->assertJsonPath('2.order', 3);
	}
}
