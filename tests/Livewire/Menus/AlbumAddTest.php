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

namespace Tests\Livewire\Menus;

use App\Livewire\Components\Menus\AlbumAdd;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumAddTest extends BaseLivewireTest
{
	public function testMenu(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
			->assertViewIs('livewire.context-menus.album-add')
			->assertStatus(200);
	}

	public function testOpenAlbumCreateModalModal(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
		->call('openAlbumCreateModal')
		->assertDispatched('closeContextMenu')
		->assertDispatched('openModal', 'forms.album.create');
	}

	public function testOpenTagAlbumCreateModal(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
		->call('openTagAlbumCreateModal')
		->assertDispatched('closeContextMenu')
		->assertDispatched('openModal', 'forms.album.create-tag');
	}

	public function testOpenImportFromServerModal(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
		->call('openImportFromServerModal')
		->assertDispatched('closeContextMenu')
		->assertDispatched('openModal', 'forms.add.import-from-server');
	}

	public function testOpenImportFromUrlModal(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
		->call('openImportFromUrlModal')
		->assertDispatched('closeContextMenu')
		->assertDispatched('openModal', 'forms.add.import-from-url');
	}

	public function testOpenUploadModal(): void
	{
		Livewire::test(AlbumAdd::class, ['params' => ['parentID' => null]])
		->call('openUploadModal')
		->assertDispatched('closeContextMenu')
		->assertDispatched('openModal', 'forms.add.upload');
	}
}
