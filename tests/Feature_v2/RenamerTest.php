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
use App\Metadata\Renamer\Renamer;
use App\Models\Configs;
use App\Models\RenamerRule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

class RenamerTest extends BaseApiWithDataTest
{
	use RequireSE;
	use DatabaseTransactions;

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

	public function testRenamerRules(): void
	{
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

	public function testRenamerHandleMany(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('test_rule')
			->description('Test Rule')
			->needle('test')
			->replacement('TEST')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->admin->id);
		$inputs = ['test one', 'test two', 'test three'];
		$outputs = $renamer->handleMany($inputs);

		self::assertEquals(['TEST one', 'TEST two', 'TEST three'], $outputs);
		$this->resetSe();
	}

	public function testRenamerHandleManyDisabled(): void
	{
		$renamer = new Renamer($this->admin->id);
		$inputs = ['test one', 'test two', 'test three'];
		$outputs = $renamer->handleMany($inputs);

		self::assertEquals($inputs, $outputs);
	}

	public function testRenamerModeFirst(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('first_rule')
			->description('First Mode Test')
			->needle('test')
			->replacement('TEST')
			->mode(RenamerModeType::FIRST)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'test and test again';
		$output = $renamer->handle($input);
		self::assertEquals('TEST and test again', $output);
		$this->resetSe();
	}

	public function testRenamerModeTrim(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('trim_rule')
			->description('Trim Mode Test')
			->needle('') // Not used in TRIM mode
			->replacement('') // Not used in TRIM mode
			->mode(RenamerModeType::TRIM)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = '  test string  ';
		$output = $renamer->handle($input);
		self::assertEquals('test string', $output);
		$this->resetSe();
	}

	public function testRenamerModeLower(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('lower_rule')
			->description('Lower Mode Test')
			->needle('') // Not used in LOWER mode
			->replacement('') // Not used in LOWER mode
			->mode(RenamerModeType::LOWER)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'TEST STRING';
		$output = $renamer->handle($input);
		self::assertEquals('test string', $output);
		$this->resetSe();
	}

	public function testRenamerModeUpper(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('upper_rule')
			->description('Upper Mode Test')
			->needle('') // Not used in UPPER mode
			->replacement('') // Not used in UPPER mode
			->mode(RenamerModeType::UPPER)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('TEST STRING', $output);
		$this->resetSe();
	}

	public function testRenamerModeUcWords(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('ucwords_rule')
			->description('UcWords Mode Test')
			->needle('') // Not used in UCWORDS mode
			->replacement('') // Not used in UCWORDS mode
			->mode(RenamerModeType::UCWORDS)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('Test String', $output);
		$this->resetSe();
	}

	public function testRenamerModeUcFirst(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('ucfirst_rule')
			->description('UcFirst Mode Test')
			->needle('') // Not used in UCFIRST mode
			->replacement('') // Not used in UCFIRST mode
			->mode(RenamerModeType::UCFIRST)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('Test string', $output);
		$this->resetSe();
	}

	public function testRenamerEnforcedBefore(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('renamer_enforced_before', '1');
		Configs::set('owner_id', $this->admin->id);

		// Admin rule (will be applied first due to before enforcement)
		$rule_admin = RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('admin_rule')
			->description('Admin Rule')
			->needle('test')
			->replacement('ADMIN')
			->mode(RenamerModeType::ALL)
			->create();

		// User rule (will be applied after admin rule)
		$rule_user = RenamerRule::factory()
			->order(1)
			->owner_id($this->userMayUpload1->id)
			->rule('user_rule')
			->description('User Rule')
			->needle('ADMIN')
			->replacement('USER')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->userMayUpload1->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(2, $renamer->getRules()->count());

		// Admin rule should be first, user rule second
		self::assertEquals($rule_admin->id, $renamer->getRules()->first()->id);
		self::assertEquals($rule_user->id, $renamer->getRules()->last()->id);

		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('USER string', $output);
		$this->resetSe();
	}

	public function testRenamerEnforcedAfter(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('renamer_enforced_after', '1');
		Configs::set('owner_id', $this->admin->id);

		// Admin rule (will be applied after user rule)
		$rule_admin = RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('admin_rule')
			->description('Admin Rule')
			->needle('USER')
			->replacement('ADMIN')
			->mode(RenamerModeType::ALL)
			->create();

		// User rule (will be applied first)
		$rule_user = RenamerRule::factory()
			->order(1)
			->owner_id($this->userMayUpload1->id)
			->rule('user_rule')
			->description('User Rule')
			->needle('test')
			->replacement('USER')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->userMayUpload1->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(2, $renamer->getRules()->count());

		// User rule should be first, admin rule second
		self::assertEquals($rule_user->id, $renamer->getRules()->first()->id);
		self::assertEquals($rule_admin->id, $renamer->getRules()->last()->id);

		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('ADMIN string', $output);
		$this->resetSe();
	}

	public function testRenamerEnforcedAfterOnlyAppliesToUsersWithRules(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('renamer_enforced_after', '1');
		Configs::set('owner_id', $this->admin->id);

		// Admin rule
		$rule_admin = RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('admin_rule')
			->description('Admin Rule')
			->needle('test')
			->replacement('ADMIN')
			->mode(RenamerModeType::ALL)
			->create();

		// User has no rules
		$renamer = new Renamer($this->userMayUpload1->id);
		self::assertTrue($renamer->is_enabled);
		self::assertEquals(0, $renamer->getRules()->count());

		$input = 'test string';
		$output = $renamer->handle($input);
		// Should return unchanged since enforced_after only applies when user has rules
		self::assertEquals('test string', $output);
		$this->resetSe();
	}

	public function testRenamerInvalidRegex(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		// Invalid regex pattern
		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('invalid_regex')
			->description('Invalid Regex Rule')
			->needle('/[invalid')
			->replacement('REPLACED')
			->mode(RenamerModeType::REGEX)
			->create();

		$renamer = new Renamer($this->admin->id);
		$input = 'test string';
		$output = $renamer->handle($input);

		// Should return input unchanged due to exception handling
		self::assertEquals('test string', $output);
		$this->resetSe();
	}

	public function testRenamerRuleOrderProcessing(): void
	{
		$this->requireSe();
		Configs::set('renamer_enabled', '1');
		Configs::set('owner_id', $this->admin->id);

		// Rule with order 3 (should be processed last)
		RenamerRule::factory()
			->order(3)
			->owner_id($this->admin->id)
			->rule('third_rule')
			->description('Third Rule')
			->needle('SECOND')
			->replacement('THIRD')
			->mode(RenamerModeType::ALL)
			->create();

		// Rule with order 1 (should be processed first)
		RenamerRule::factory()
			->order(1)
			->owner_id($this->admin->id)
			->rule('first_rule')
			->description('First Rule')
			->needle('test')
			->replacement('FIRST')
			->mode(RenamerModeType::ALL)
			->create();

		// Rule with order 2 (should be processed second)
		RenamerRule::factory()
			->order(2)
			->owner_id($this->admin->id)
			->rule('second_rule')
			->description('Second Rule')
			->needle('FIRST')
			->replacement('SECOND')
			->mode(RenamerModeType::ALL)
			->create();

		$renamer = new Renamer($this->admin->id);
		self::assertEquals(3, $renamer->getRules()->count());

		$input = 'test string';
		$output = $renamer->handle($input);
		self::assertEquals('THIRD string', $output);
		$this->resetSe();
	}
}