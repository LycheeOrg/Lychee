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
use App\Models\Configs;
use App\Models\RenamerRule;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RenamerRulesTestTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		Configs::set('renamer_enabled', true);
	}

	public function tearDown(): void
	{
		$this->resetSe();
		parent::tearDown();
	}

	public function testTestRenamerRuleUnauthorized(): void
	{
		$response = $this->postJson('Renamer::test', [
			'candidate' => 'test_string',
			'is_photo' => true,
			'is_album' => true,
		]);
		$this->assertUnauthorized($response);
	}

	public function testTestRenamerRuleForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->postJson('Renamer::test', [
			'candidate' => 'test_string',
			'is_photo' => true,
			'is_album' => true,
		]);
		$this->assertForbidden($response);
	}

	public function testTestRenamerRuleUnprocessable(): void
	{
		// Test without required candidate field
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', []);
		$this->assertUnprocessable($response);

		// Test with empty candidate
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => '',
			'is_photo' => true,
			'is_album' => true,
		]);
		$this->assertUnprocessable($response);
	}

	public function testTestRenamerRuleWithNoRules(): void
	{
		$candidate = 'IMG_1234.jpg';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $candidate); // Should be unchanged with no rules
	}

	public function testTestRenamerRuleWithSingleRule(): void
	{
		// Create a single renamer rule
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_IMG',
			'description' => 'Replace IMG with Photo',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'IMG_1234.jpg';
		$expected = 'Photo_1234.jpg';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}

	public function testTestRenamerRuleWithMultipleRules(): void
	{
		// Create multiple renamer rules in order
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_IMG',
			'description' => 'Replace IMG with Photo',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_jpg',
			'description' => 'Replace .jpg with .jpeg',
			'needle' => '.jpg',
			'replacement' => '.jpeg',
			'mode' => RenamerModeType::ALL,
			'order' => 2,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'IMG_1234.jpg';
		$expected = 'Photo_1234.jpeg';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}

	public function testTestRenamerRuleWithDisabledRule(): void
	{
		// Create an enabled rule and a disabled rule
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_IMG',
			'description' => 'Replace IMG with Photo',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_jpg',
			'description' => 'Replace .jpg with .jpeg',
			'needle' => '.jpg',
			'replacement' => '.jpeg',
			'mode' => RenamerModeType::ALL,
			'order' => 2,
			'is_enabled' => false, // This rule is disabled
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'IMG_1234.jpg';
		$expected = 'Photo_1234.jpg'; // Only first rule should apply

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}

	public function testTestRenamerRuleWithDifferentModes(): void
	{
		// Test FIRST mode
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_first_a',
			'description' => 'Replace first occurrence of a with X',
			'needle' => 'a',
			'replacement' => 'X',
			'mode' => RenamerModeType::FIRST,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'banana_apple';
		$expected = 'bXnana_apple'; // Only first 'a' should be replaced

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}

	public function testTestRenamerRuleWithRegexMode(): void
	{
		// Create a regex rule to remove all digits
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'remove_digits',
			'description' => 'Remove all digits',
			'needle' => '/\d+/',
			'replacement' => '',
			'mode' => RenamerModeType::REGEX,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'IMG_1234_photo_5678.jpg';
		$expected = 'IMG__photo_.jpg';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}

	public function testTestRenamerRuleWithInvalidRegex(): void
	{
		$invalid = '[invalid_regex';
		// Create a rule with invalid regex pattern
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'invalid_regex',
			'description' => 'Invalid regex pattern',
			'needle' => '/' . $invalid . '/', // Invalid regex but needs to be written like this other otherwise it breaks the syntax highlighter (lol)
			'replacement' => 'fixed',
			'mode' => RenamerModeType::REGEX,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'test_string';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $candidate); // Should return original on regex error
	}

	public function testTestRenamerRuleIsolatedByUser(): void
	{
		// Create a rule for userMayUpload1
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'user1_rule',
			'description' => 'User 1 rule',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		// Create a rule for userMayUpload2
		RenamerRule::create([
			'owner_id' => $this->userMayUpload2->id,
			'rule' => 'user2_rule',
			'description' => 'User 2 rule',
			'needle' => 'IMG_',
			'replacement' => 'Picture_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		$candidate = 'IMG_1234.jpg';

		// Test with user1 - should use user1's rule
		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', 'Photo_1234.jpg');

		// Test with user2 - should use user2's rule
		$response = $this->actingAs($this->userMayUpload2)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', 'Picture_1234.jpg');
	}

	public function testTestRenamerRuleAsAdmin(): void
	{
		// Create a rule for regular user
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'user_rule',
			'description' => 'User rule',
			'needle' => 'IMG_',
			'replacement' => 'Photo_',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		// Admin should be able to test renamer rules (using their own rules)
		$candidate = 'IMG_1234.jpg';

		$response = $this->actingAs($this->admin)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $candidate); // Admin has no rules, so no change
	}

	public function testTestRenamerRuleWithLongString(): void
	{
		// Create a simple replacement rule
		RenamerRule::create([
			'owner_id' => $this->userMayUpload1->id,
			'rule' => 'replace_test',
			'description' => 'Replace test with TEST',
			'needle' => 'test',
			'replacement' => 'TEST',
			'mode' => RenamerModeType::ALL,
			'order' => 1,
			'is_enabled' => true,
			'is_photo_rule' => true,
			'is_album_rule' => true,
		]);

		// Test with a longer string (within the 1000 character limit)
		$candidate = str_repeat('test_', 100) . 'file.jpg'; // Should be under 1000 chars
		$expected = str_repeat('TEST_', 100) . 'file.jpg';

		$response = $this->actingAs($this->userMayUpload1)->postJson('Renamer::test', [
			'candidate' => $candidate,
			'is_photo' => true,
			'is_album' => true,
		]);

		$this->assertOk($response);
		$response->assertJsonPath('original', $candidate);
		$response->assertJsonPath('result', $expected);
	}
}
