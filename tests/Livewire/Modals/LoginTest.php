<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Modals;

use App\Livewire\Components\Modals\Login;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class LoginTest extends BaseLivewireTest
{
	public function testLogin(): void
	{
		Livewire::test(Login::class)
			->assertViewIs('livewire.modals.login')
			->call('submit')
			->assertHasErrors('password', ['required'])
			->assertHasErrors('username', ['required'])
			->set('username', 'admin')
			->call('submit')
			->assertHasErrors('password', ['required'])
			->set('password', '123456')
			->call('submit')
			->assertHasErrors('wrongLogin')
			->set('password', 'password')
			->call('submit')
			->assertDispatched('login-close')
			->assertDispatched('reloadPage');
	}
}
