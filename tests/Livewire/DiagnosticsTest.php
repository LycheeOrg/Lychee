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

namespace Tests\Livewire;

use App\Livewire\Components\Modules\Diagnostics\Configurations;
use App\Livewire\Components\Modules\Diagnostics\Infos;
use App\Livewire\Components\Modules\Diagnostics\Space;
use App\Livewire\Components\Pages\Diagnostics;
use App\Models\User;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class DiagnosticsTest extends BaseLivewireTest
{
	public function testDiagnosticsLoggedOut(): void
	{
		Livewire::test(Diagnostics::class)
			->assertViewIs('livewire.pages.diagnostics')
			->assertSeeLivewire(Configurations::class)
			->assertSeeLivewire(Space::class)
			->assertSeeLivewire(Infos::class)
			->assertSeeInOrder([
				'Error: You must have administrator rights to see this.',
				'Error: You must have administrator rights to see this.',
				'Error: You must have administrator rights to see this.']);
	}

	public function testDiagnosticsLogged(): void
	{
		$admin = User::findOrFail(1);

		Livewire::actingAs($admin)->test(Diagnostics::class)
			->assertViewIs('livewire.pages.diagnostics')
			->assertSeeLivewire(Configurations::class)
			->assertSeeLivewire(Space::class)
			->assertSeeLivewire(Infos::class)
			->assertDontSee('Error: You must have administrator rights to see this.');
	}
}
