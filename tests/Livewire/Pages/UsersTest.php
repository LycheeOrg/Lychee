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

namespace Tests\Livewire\Pages;

use App\Livewire\Components\Pages\Users;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;

class UsersTest extends BaseLivewireTest
{
	use RequiresEmptyUsers;

	private string $component = Users::class;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.pages.users')
			->assertCount('users', 1);

		Livewire::actingAs($this->admin)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}

	public function testCreate(): void
	{
		User::where('username', '=', 'user1')->delete();

		$this->assertEquals(1, User::count());
		$this->assertEquals(0, User::where('username', '=', 'user1')->count());

		Livewire::actingAs($this->admin)->test(Users::class)
			->set('username', 'user1')
			->set('password', 'password')
			->call('create')
			->assertCount('users', 2);

		$this->assertEquals(2, User::count());
	}

	public function testDelete(): void
	{
		/** @var User $user  */
		$user = User::factory()->create();

		Livewire::actingAs($this->admin)->test(Users::class)
			->call('delete', $user->id)
			->assertCount('users', 1);
	}

	public function testForbiddenDelete(): void
	{
		Auth::login($this->admin);
		Livewire::test(Users::class)
			->call('delete', $this->admin->id)
			->assertStatus(403);
	}
}
