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
use Illuminate\Http\Response;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenamerRulesCreateTest extends BaseApiWithDataTest
{
	public function testCreateRenamerRuleUnauthorized(): void
	{
		$response = $this->postJson('Renamer', [
			'rule' => 'test_rule',
			'description' => 'Test rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertUnauthorized($response);
	}

	public function testCreateRenamerRuleForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->postJson('Renamer', [
			'rule' => 'test_rule',
			'description' => 'Test rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testCreateRenamerRuleUnprocessable(): void
	{
		// Test without required fields
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', []);
		$this->assertUnprocessable($response);

		// Test with invalid mode
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule',
			'description' => 'Test rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'invalid_mode',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertUnprocessable($response);

		// Test with invalid order (must be at least 1)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule',
			'description' => 'Test rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 0,
			'is_enabled' => true,
		]);
		$this->assertUnprocessable($response);

		// Test with invalid is_enabled (must be boolean)
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule',
			'description' => 'Test rule',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => 'invalid',
		]);
		$this->assertUnprocessable($response);
	}

	public function testCreateRenamerRuleAuthorizedWithAllModes(): void
	{
		// Test with ALL mode
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule_all',
			'description' => 'Test rule with ALL mode',
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$response->assertJsonPath('rule', 'test_rule_all');
		$response->assertJsonPath('description', 'Test rule with ALL mode');
		$response->assertJsonPath('needle', 'old');
		$response->assertJsonPath('replacement', 'new');
		$response->assertJsonPath('mode', 'all');
		$response->assertJsonPath('order', 1);
		$response->assertJsonPath('is_enabled', true);
		$response->assertJsonPath('owner_id', $this->userMayUpload1->id);

		// Test with FIRST mode
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule_first',
			'description' => 'Test rule with FIRST mode',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => 'first',
			'order' => 2,
			'is_enabled' => false,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$response->assertJsonPath('rule', 'test_rule_first');
		$response->assertJsonPath('mode', 'first');
		$response->assertJsonPath('order', 2);
		$response->assertJsonPath('is_enabled', false);

		// Test with REGEX mode
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule_regex',
			'description' => 'Test rule with REGEX mode',
			'needle' => '\d{4}-\d{2}-\d{2}',
			'replacement' => 'DATE',
			'mode' => 'regex',
			'order' => 3,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$response->assertJsonPath('rule', 'test_rule_regex');
		$response->assertJsonPath('mode', 'regex');
		$response->assertJsonPath('needle', '\d{4}-\d{2}-\d{2}');
		$response->assertJsonPath('order', 3);
	}

	public function testCreateRenamerRuleWithNullDescription(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'test_rule_no_desc',
			'description' => null,
			'needle' => 'old',
			'replacement' => 'new',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$response->assertJsonPath('rule', 'test_rule_no_desc');
		$response->assertJsonPath('description', '');
	}

	public function testCreateRenamerRuleAsAdmin(): void
	{
		$response = $this->actingAs($this->admin)->postJson('Renamer', [
			'rule' => 'admin_rule',
			'description' => 'Admin rule',
			'needle' => 'admin',
			'replacement' => 'ADMIN',
			'mode' => 'all',
			'order' => 1,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);
		$response->assertJsonPath('rule', 'admin_rule');
		$response->assertJsonPath('owner_id', $this->admin->id);
	}

	public function testCreateRenamerRuleStoresInDatabase(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer', [
			'rule' => 'db_test_rule',
			'description' => 'Database test rule',
			'needle' => 'test',
			'replacement' => 'tested',
			'mode' => 'all',
			'order' => 5,
			'is_enabled' => true,
		]);
		$this->assertStatus($response, Response::HTTP_CREATED);

		$ruleId = $response->json('id');
		$this->assertDatabaseHas('renamer_rules', [
			'id' => $ruleId,
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'db_test_rule',
			'description' => 'Database test rule',
			'needle' => 'test',
			'replacement' => 'tested',
			'mode' => RenamerModeType::ALL->value,
			'order' => 5,
			'is_enabled' => true,
		]);
	}
}
