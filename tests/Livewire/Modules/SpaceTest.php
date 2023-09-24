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

namespace Tests\Livewire\Modules;

use App\Livewire\Components\Modules\Diagnostics\Space;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SpaceTest extends BaseLivewireTest
{
	private string $component = Space::class;

	public function setUp(): void
	{
		parent::setUp();
	}

	public function tearDown(): void
	{
		parent::tearDown();
	}

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertViewIs('livewire.modules.diagnostics.space')
			->assertSee('Error: You must have administrator rights to see this.')
			->assertOk();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.modules.diagnostics.space')
			->assertDontSee('Error: You must have administrator rights to see this.')
			->assertOk()
			->call('getSize')
			->assertOk()
			->assertDontSee('Error: You must have administrator rights to see this.');
	}
}
