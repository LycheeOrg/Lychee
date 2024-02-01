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

use App\Livewire\Components\Modules\Diagnostics\Configurations;
use App\Livewire\Components\Modules\Diagnostics\Errors;
use App\Livewire\Components\Modules\Diagnostics\Infos;
use App\Livewire\Components\Modules\Diagnostics\Optimize;
use App\Livewire\Components\Modules\Diagnostics\Space;
use App\Livewire\Components\Pages\Diagnostics;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class DiagnosticsTest extends BaseLivewireTest
{
	public function testDiagnosticsLoggedOut(): void
	{
		Livewire::test(Diagnostics::class)
			->assertViewIs('livewire.pages.diagnostics')
			->assertSeeLivewire(Errors::class)
			->assertSeeLivewire(Configurations::class)
			->assertSeeLivewire(Space::class)
			->assertSeeLivewire(Optimize::class)
			->assertSeeLivewire(Infos::class)
			->assertSee('Diagnostics')
			->assertDontSee('System Information')
			->assertDontSee('Space Usage')
			->assertDontSee('Optimize DB')
			->assertDontSee('Config Information');
	}

	public function testDiagnosticsLogged(): void
	{
		$diagnostics = Livewire::actingAs($this->admin)->test(Diagnostics::class, ['lazy' => false])
			->assertViewIs('livewire.pages.diagnostics')
			->assertSeeLivewire(Errors::class)
			->assertSeeLivewire(Configurations::class)
			->assertSeeLivewire(Space::class)
			->assertSeeLivewire(Optimize::class)
			->assertSeeLivewire(Infos::class)
			->assertSeeInOrder(['Diagnostics', 'System Information', 'Space Usage', 'Optimize DB', 'Config Information']);

		$diagnostics->dispatch('reloadPage')
			->assertStatus(200);

		Livewire::actingAs($this->admin)->test(Diagnostics::class)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}
}
