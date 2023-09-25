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

namespace Tests\Livewire\Forms;

use App\Enum\Livewire\NotificationType;
use App\Livewire\Components\Forms\Settings\Base\BooleanSetting;
use App\Livewire\Components\Forms\Settings\Base\StringSetting;
use App\Livewire\Components\Forms\Settings\SetAlbumDecorationOrientationSetting;
use App\Livewire\Components\Forms\Settings\SetAlbumSortingSetting;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SettingsTest extends BaseLivewireTest
{
	public function testDropdown(): void
	{
		Livewire::actingAs($this->admin)->test(SetAlbumDecorationOrientationSetting::class)
			->assertOk()
			->assertViewIs('livewire.forms.settings.drop-down')
			->set('value', 'column')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);

		Livewire::actingAs($this->admin)->test(SetAlbumDecorationOrientationSetting::class)
		->assertOk()
		->assertViewIs('livewire.forms.settings.drop-down')
		->set('value', 'something')
		->assertNotDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);
	}

	public function testDoubleDropdown(): void
	{
		Livewire::actingAs($this->admin)->test(SetAlbumSortingSetting::class)
			->assertOk()
			->assertViewIs('livewire.forms.settings.double-drop-down')
			->set('value1', 'title')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('value2', 'DESC')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);

		Livewire::actingAs($this->admin)->test(SetAlbumSortingSetting::class)
			->assertOk()
			->assertViewIs('livewire.forms.settings.double-drop-down')
			->set('value1', 'something')
			->assertNotDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value])
			->set('value1', 'title')
			->set('value2', 'OTHER')
			->assertNotDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);
	}

	public function testBoolean(): void
	{
		Configs::set('public_search', false);

		Livewire::actingAs($this->admin)->test(BooleanSetting::class,
			['description' => 'PUBLIC_SEARCH_TEXT', 'name' => 'public_search'])
			->assertOk()
			->assertViewIs('livewire.forms.settings.toggle')
			->assertSet('flag', false)
			->set('flag', true)
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);
	}

	public function testString(): void
	{
		Livewire::actingAs($this->admin)->test(StringSetting::class, [
			'description' => 'DROPBOX_TEXT',
			'placeholder' => 'SETTINGS_DROPBOX_KEY',
			'action' => 'DROPBOX_TITLE',
			'name' => 'dropbox_key',
		])
			->assertOk()
			->assertViewIs('livewire.forms.settings.input')
			->assertSet('value', '')
			->set('value', 'api')
			->call('save')
			->assertDispatched('notify', ['msg' => __('lychee.CHANGE_SUCCESS'), 'type' => NotificationType::SUCCESS->value]);
	}
}
