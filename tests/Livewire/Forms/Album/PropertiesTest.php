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

namespace Tests\Livewire\Forms\Album;

use App\Livewire\Components\Forms\Album\Properties;
use App\Models\Album;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class PropertiesTest extends BaseLivewireTest
{
	public function testPropertiesLoggedOut(): void
	{
		Livewire::test(Properties::class, ['album' => $this->album1])->assertForbidden();
	}

	public function testPropertiesLoggedIn(): void
	{
		Livewire::actingAs($this->admin)->test(Properties::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.properties')
			->call('submit')
			->assertOk()
			->assertDispatched('notify', self::notifySuccess());

		Livewire::actingAs($this->admin)->test(Properties::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.properties')
			->set('license', 'WTFPL')
			->call('submit')
			->assertOk()
			->assertNotDispatched('notify', self::notifySuccess());
	}

	public function testSetCopyright(): void
	{
		Livewire::actingAs($this->admin)->test(Properties::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.properties')
			->set('copyright', 'something')
			->call('submit')
			->assertOk()
			->assertDispatched('notify', self::notifySuccess());

		/** @var Album $album */
		$album = Album::findOrFail($this->album1->id);
		$this->assertEquals('something', $album->copyright);

		Livewire::actingAs($this->admin)->test(Properties::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.properties')
			->set('copyright', '')
			->call('submit')
			->assertOk()
			->assertDispatched('notify', self::notifySuccess());

		/** @var Album $album */
		$album = Album::findOrFail($this->album1->id);
		$this->assertEquals(null, $album->copyright);
	}
}
