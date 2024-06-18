<?php

declare(strict_types=1);

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Livewire\Modules;

use App\Livewire\Components\Modules\Users\UserLine;
use App\Models\User;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class UserLineTest extends BaseLivewireTest
{
	private string $component = UserLine::class;

	public function testLoggedOut(): void
	{
		$user = User::factory()->create();
		Livewire::test($this->component, ['user' => $user])
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		/** @var User $user */
		$user = User::factory()->create();

		Livewire::actingAs($this->admin)->test($this->component, ['user' => $user])
			->assertViewIs('livewire.modules.users.user-line')
			->assertSet('username', $user->username)
			->assertSet('password', '')
			->assertSet('may_upload', false)
			->assertSet('may_edit_own_settings', true)
			->assertSet('id', $user->id);
	}

	public function testEdit(): void
	{
		$user = User::factory()->create();

		Livewire::actingAs($this->admin)->test($this->component, ['user' => $user])
			->assertViewIs('livewire.modules.users.user-line')
			->set('username', 'user1')
			->set('may_upload', true)
			->set('may_edit_own_settings', false)
			->call('save')
			->assertSet('username', 'user1')
			->assertSet('may_upload', true)
			->assertSet('may_edit_own_settings', false);

		Livewire::actingAs($this->admin)->test($this->component, ['user' => $user])
			->set('username', '')
			->call('save')
			->assertDispatched('notify'); // notify only on failure.

		Livewire::actingAs($this->admin)->test($this->component, ['user' => $user])
			->set('username', 'admin') // cannot use an existing name.
			->call('save')
			->assertStatus(409);
	}
}
