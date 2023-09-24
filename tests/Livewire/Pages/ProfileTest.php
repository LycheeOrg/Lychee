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

use App\Livewire\Components\Pages\Profile;
use App\Models\User;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyUsers;
use Tests\Livewire\Base\BaseLivewireTest;

class ProfileTest extends BaseLivewireTest
{
	use RequiresEmptyUsers;

	private User $powerlessUser;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyUsers();
		$this->powerlessUser = User::factory()->create(['may_edit_own_settings' => false]);
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyUsers();
		parent::tearDown();
	}

	private string $component = Profile::class;

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.pages.profile');

		Livewire::actingAs($this->admin)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}

	public function testLoggedInPowerless(): void
	{
		Livewire::actingAs($this->powerlessUser)->test($this->component)
			->assertForbidden();
	}
}
