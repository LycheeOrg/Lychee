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

use App\Livewire\Components\Pages\AllSettings;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AllSettingsTest extends BaseLivewireTest
{
	private string $component = AllSettings::class;

	public function testLoggedOut(): void
	{
		Livewire::test($this->component)
			->assertForbidden();
	}

	public function testLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test($this->component)
			->assertViewIs('livewire.pages.all-settings')
			->call('openConfirmSave')
			->assertDispatched('openModal');

		Livewire::actingAs($this->admin)->test($this->component)
			->dispatch('saveAll')
			->assertStatus(200);

		Livewire::actingAs($this->admin)->test($this->component)
			->call('back')
			->assertDispatched('closeLeftMenu')
			->assertRedirect(route('settings'));
	}

	public function testLoggedInWithErrors(): void
	{
		$idx = Configs::orderBy('cat', 'asc')->get()->search(function (Configs $item, $key) {
			return $item->key === 'check_for_updates';
		});

		Livewire::actingAs($this->admin)->test($this->component)
			->set('form.values.' . $idx, '2')
			->dispatch('saveAll')
			->assertHasErrors('form.values.' . $idx);

		$this->assertNotEquals(2, Configs::getValueAsInt('check_for_updates'));
	}
}
