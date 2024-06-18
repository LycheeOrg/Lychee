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

namespace Tests\Livewire\Forms;

use App\Livewire\Components\Forms\Confirms\SaveAll;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class ConfirmTest extends BaseLivewireTest
{
	public function testConfirmLoggedOut(): void
	{
		Livewire::test(SaveAll::class)->assertForbidden();
	}

	public function testConfirmLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(SaveAll::class)
			->assertOk()
			->assertViewIs('livewire.forms.confirms.save-all')
			->call('confirm')
			->assertDispatched('saveAll')
			->assertDispatched('closeModal');

		Livewire::actingAs($this->admin)->test(SaveAll::class)
		->assertOk()
		->assertViewIs('livewire.forms.confirms.save-all')
		->call('close')
		->assertNotDispatched('saveAll')
		->assertDispatched('closeModal');
	}
}
