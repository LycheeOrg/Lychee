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

namespace Tests\Unit\Actions\User;

use App\Actions\User\Create;
use App\Exceptions\ConflictingPropertyException;
use App\Exceptions\InvalidPropertyException;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

/**
 * Feature 091 – the username falls back to the email address (FR-091-08, FR-091-09).
 */
class CreateUsernameFallbackTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private Create $create;

	protected function setUp(): void
	{
		parent::setUp();
		$this->create = resolve(Create::class);
	}

	/**
	 * S-091-09 – an empty username is replaced by the email address.
	 */
	public function testEmptyUsernameFallsBackToTheEmail(): void
	{
		$user = $this->create->do('', 'a-password', 'fallback@example.com');

		self::assertSame('fallback@example.com', $user->username);
		self::assertSame('fallback@example.com', $user->email);
	}

	/**
	 * S-091-10 – an explicit username always wins over the email.
	 */
	public function testExplicitUsernameIsKept(): void
	{
		$user = $this->create->do('explicit-name', 'a-password', 'explicit@example.com');

		self::assertSame('explicit-name', $user->username);
		self::assertSame('explicit@example.com', $user->email);
	}

	/**
	 * S-091-13 – neither a username nor an email is an invalid request.
	 */
	public function testEmptyUsernameWithoutEmailThrows(): void
	{
		$this->expectException(InvalidPropertyException::class);
		$this->create->do('', 'a-password', null);
	}

	/**
	 * S-091-13 – an empty email is treated like a missing one.
	 */
	public function testEmptyUsernameWithEmptyEmailThrows(): void
	{
		$this->expectException(InvalidPropertyException::class);
		$this->create->do('', 'a-password', '');
	}

	/**
	 * S-091-18 – with the setting on, a username that is already another
	 * account's email address is refused: that identifier would address two
	 * accounts. This is the direction the feature tests do not exercise.
	 */
	public function testUsernameMatchingAnotherAccountsEmailIsRefused(): void
	{
		Configs::set('email_login_enabled', '1');
		User::factory()->create(['email' => 'owner@example.com']);

		$this->expectException(ConflictingPropertyException::class);
		$this->create->do('owner@example.com', 'a-password', 'something-else@example.com');
	}

	/**
	 * S-091-18 – with the setting off the two attributes are independent, so
	 * the same call succeeds.
	 */
	public function testUsernameMatchingAnotherAccountsEmailIsAllowedWhenSettingOff(): void
	{
		Configs::set('email_login_enabled', '0');
		User::factory()->create(['email' => 'owner@example.com']);

		$user = $this->create->do('owner@example.com', 'a-password', 'something-else@example.com');

		self::assertSame('owner@example.com', $user->username);
	}

	/**
	 * S-091-12 – the resolved username is still checked for uniqueness.
	 */
	public function testFallbackUsernameMustBeUnique(): void
	{
		$taken = new User();
		$taken->username = 'taken@example.com';
		$taken->password = 'irrelevant';
		$taken->save();

		$this->expectException(ConflictingPropertyException::class);
		$this->create->do('', 'a-password', 'taken@example.com');
	}
}
