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

namespace Tests\Unit\Rules;

use App\Models\Album;
use App\Models\User;
use App\Rules\SlugRule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class SlugRuleTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function assertPasses(SlugRule $rule, mixed $value): void
	{
		$failed = false;
		$rule->validate('slug', $value, function () use (&$failed): void { $failed = true; });
		self::assertFalse($failed, 'Expected slug "' . $value . '" to pass but it failed.');
	}

	private function assertFails(SlugRule $rule, mixed $value): void
	{
		$failed = false;
		$rule->validate('slug', $value, function () use (&$failed): void { $failed = true; });
		self::assertTrue($failed, 'Expected slug "' . $value . '" to fail but it passed.');
	}

	public function testNullAndEmptyPassAsOptional(): void
	{
		$rule = new SlugRule();
		$this->assertPasses($rule, null);
		$this->assertPasses($rule, '');
	}

	public function testValidSlugs(): void
	{
		$rule = new SlugRule();
		$this->assertPasses($rule, 'my-album');
		$this->assertPasses($rule, 'architecture');
		$this->assertPasses($rule, 'my-vacation-2025');
		$this->assertPasses($rule, 'a_b');
		$this->assertPasses($rule, 'ab');
		$this->assertPasses($rule, str_repeat('a', 250));
	}

	public function testInvalidFormat(): void
	{
		$rule = new SlugRule();
		$this->assertFails($rule, 'My-Album');       // uppercase
		$this->assertFails($rule, 'café!');           // special chars
		$this->assertFails($rule, '2025-trip');       // leading digit
		$this->assertFails($rule, '-bad');            // leading hyphen
		$this->assertFails($rule, 'a');               // single char (min 2)
		$this->assertFails($rule, str_repeat('a', 251)); // too long
		$this->assertFails($rule, 'hello world');     // space
		$this->assertFails($rule, 'hello.world');     // dot
	}

	public function testReservedSmartAlbumTypes(): void
	{
		$rule = new SlugRule();
		$this->assertFails($rule, 'unsorted');
		$this->assertFails($rule, 'recent');
		$this->assertFails($rule, 'highlighted');
		$this->assertFails($rule, 'on_this_day');
		$this->assertFails($rule, 'best_pictures');
		$this->assertFails($rule, 'my_rated_pictures');
		$this->assertFails($rule, 'my_best_pictures');
	}

	public function testUniqueness(): void
	{
		// Create a user and album with a slug
		$user = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();
		$album->base_class->slug = 'test-unique-slug';
		$album->base_class->save();

		// Another album trying the same slug should fail
		$rule = new SlugRule();
		$this->assertFails($rule, 'test-unique-slug');

		// The same album should be able to keep its own slug (exclude self)
		$rule_with_exclude = new SlugRule($album->id);
		$this->assertPasses($rule_with_exclude, 'test-unique-slug');

		// A different slug should pass
		$this->assertPasses($rule, 'different-slug');
	}
}
