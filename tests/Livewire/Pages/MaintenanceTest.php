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

namespace Tests\Livewire\Pages;

use App\Livewire\Components\Modules\Maintenance\Cleaning;
use App\Livewire\Components\Modules\Maintenance\FixJobs;
use App\Livewire\Components\Modules\Maintenance\Optimize;
use App\Livewire\Components\Modules\Maintenance\Update;
use App\Livewire\Components\Pages\Maintenance;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class MaintenanceTest extends BaseLivewireTest
{
	public function testDiagnosticsLoggedOut(): void
	{
		Livewire::test(Maintenance::class)
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload1)->test(Maintenance::class)
		->assertForbidden();
	}

	public function testMaintenanceLogged(): void
	{
		Livewire::actingAs($this->admin)->test(Maintenance::class)
			->assertViewIs('livewire.pages.maintenance')
			->assertSeeLivewire(Cleaning::class)
			->assertSeeLivewire(Optimize::class)
			->assertSeeLivewire(Update::class)
			->assertSeeLivewire(FixJobs::class)
			->assertSee('Maintenance');

		Livewire::actingAs($this->admin)->test(Maintenance::class)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('livewire-gallery'));
	}
}
