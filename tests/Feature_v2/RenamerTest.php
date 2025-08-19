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

namespace Tests\Feature_v2;

use App\Enum\RenamerModeType;
use App\Metadata\Renamer;
use App\Models\Configs;
use App\Models\RenamerRule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

class RenamerTest extends BaseApiWithDataTest
{
	use RequireSE;
	use DatabaseTransactions;

	// protected function setUp(): void
	// {
	// 	parent::setUp();
	// 	$this->watermarkerCheck = new WatermarkerEnabledCheck();
	// 	$this->data = [];
	// 	$this->next = function (array $data) {
	// 		return $data;
	// 	};

	// 	$this->requireSe();
	// }

	// protected function tearDown(): void
	// {
	// 	$this->resetSe();
	// 	parent::tearDown();
	// }

	public function testRenamerDisabledByDefault(): void
	{
		$renamer = new Renamer(2);
		self::assertFalse($renamer->is_enabled);
	}

	public function testRenamerEnabled(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		$renamer = new Renamer($this->admin->id);
		self::assertTrue($renamer->is_enabled);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('test_rule')
			->description('Test Rule')
			->needle('test')
			->replacement('TEST')
			->mode(RenamerModeType::ALL)
			->create();

		$input = 'test test test';
		$output = $renamer->handle($input);
		self::assertNotEquals('TEST TEST TEST', $output);

		$this->resetSe();
	}

	public function testRenamerRuleNoEnforcement(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		// Create a rule for the user with ID 2
		$rule_admin = RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('test_rule')
			->description('Test Rule')
			->needle('test')
			->replacement('TEST')
			->mode(RenamerModeType::ALL)
			->create();

		$rule_user = RenamerRule::factory()
			->order(1)
			->owner_id($this->userMayUpload1->id)
			->rule('test_rule_user')
			->description('Test Rule User')
			->needle('test')
			->replacement('TEST_USER')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->userMayUpload1->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(1, $renamer->getRules()->count());
		self::assertEquals($rule_user->id, $renamer->getRules()->first()->id);
		self::assertNotEquals($rule_admin->id, $renamer->getRules()->first()->id);
		$this->resetSe();
	}

	public function testRenamerRuleEnforcement(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('renamer_enforced', '1');
		Configs::set('owner_id', $this->admin->id);

		$rule_admin = RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('test_rule')
			->description('Test Rule')
			->needle('test')
			->replacement('TEST')
			->mode(RenamerModeType::ALL)
			->create();

		$rule_user = RenamerRule::factory()
			->order(1)
			->owner_id($this->userMayUpload1->id)
			->rule('test_rule_user')
			->description('Test Rule User')
			->needle('test')
			->replacement('TEST_USER')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->userMayUpload1->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(1, $renamer->getRules()->count());
		self::assertNotEquals($rule_user->id, $renamer->getRules()->first()->id);
		self::assertEquals($rule_admin->id, $renamer->getRules()->first()->id);
		$this->resetSe();
	}

	public function testRenamerRules() {
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		// Create a rule for the user with ID 2
		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('test_rule_user')
			->description('Test Rule User')
			->needle('foo')
			->replacement('FOO')
			->mode(RenamerModeType::ALL)
			->create();

		RenamerRule::factory()
			->order(2)
			->owner_id($this->admin->id)
			->rule('test_rule')
			->description('Test Rule')
			->needle('FOO')
			->replacement('BAR')
			->mode(RenamerModeType::FIRST)
			->create();

		RenamerRule::factory()
			->order(3)
			->owner_id($this->admin->id)
			->rule('test_rule_regex')
			->description('Test Rule Regex')
			->needle('/A/')
			->replacement('4')
			->mode(RenamerModeType::REGEX)
			->create();

		RenamerRule::factory()
			->order(4)
			->owner_id($this->admin->id)
			->rule('test_rule_regex')
			->description('Test Rule Regex')
			->needle('/O/')
			->replacement('0')
			->mode(RenamerModeType::REGEX)
			->create();

		$renamer = new Renamer($this->admin->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(4, $renamer->getRules()->count());
		$input = 'foo foo foo';
		$output = $renamer->handle($input);
		self::assertEquals('B4R F00 F00', $output);
		$this->resetSe();
	}
}