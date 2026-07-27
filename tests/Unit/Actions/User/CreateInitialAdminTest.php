<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Actions\User;

use App\Actions\User\CreateInitialAdmin;
use App\Exceptions\ConflictingPropertyException;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\AbstractTestCase;

class CreateInitialAdminTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testCreatesAdminWithFullPermissions(): void
	{
		User::query()->where('may_administrate', '=', true)->delete();

		$action = resolve(CreateInitialAdmin::class);
		$user = $action->do('brand-new-admin', 'super-secret-password');

		$this->assertTrue($user->may_upload);
		$this->assertTrue($user->may_edit_own_settings);
		$this->assertTrue($user->may_administrate);
		$this->assertSame('brand-new-admin', $user->username);
	}

	public function testThrowsWhenUsernameAlreadyTaken(): void
	{
		User::query()->where('may_administrate', '=', true)->delete();

		$existing = new User();
		$existing->username = 'taken-username';
		$existing->password = Hash::make('irrelevant');
		$existing->may_upload = false;
		$existing->may_edit_own_settings = false;
		$existing->may_administrate = false;
		$existing->save();

		$action = resolve(CreateInitialAdmin::class);

		$this->expectException(ConflictingPropertyException::class);
		$action->do('taken-username', 'super-secret-password');
	}
}
