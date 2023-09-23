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

use App\Livewire\Components\Menus\AlbumDropdown;
use App\Models\Album;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumDropdownTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;

	private Album $album;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();

		$this->album = new Album();
		$this->album->title = fake()->title;
		$this->album->owner_id = $this->admin->id;
		$this->album->makeRoot();
		$this->album->save();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testMenu(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->assertViewIs('livewire.context-menus.album-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->call('rename')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.rename');
	}

	public function testMerge(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->call('merge')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.merge');
	}

	public function testDelete(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->call('delete')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.delete');
	}

	public function testMove(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->call('move')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.move');
	}

	public function testDownload(): void
	{
		Livewire::test(
			AlbumDropdown::class,
			['params' => ['parentID' => null, 'albumID' => $this->album->id]]
		)
			->call('download')
			->assertDispatched('closeContextMenu')
			->assertRedirect(route('download', ['albumIDs' => $this->album->id]));
	}
}
