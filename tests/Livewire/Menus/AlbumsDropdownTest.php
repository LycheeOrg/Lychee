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

use App\Livewire\Components\Menus\AlbumsDropdown;
use App\Models\Album;
use Livewire\Livewire;
use Tests\Feature\Traits\RequiresEmptyAlbums;
use Tests\Livewire\Base\BaseLivewireTest;

class AlbumsDropdownTest extends BaseLivewireTest
{
	use RequiresEmptyAlbums;

	private Album $album1;
	private Album $album2;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyAlbums();

		$this->album1 = new Album();
		$this->album1->title = fake()->title;
		$this->album1->owner_id = $this->admin->id;
		$this->album1->makeRoot();
		$this->album1->save();

		$this->album2 = new Album();
		$this->album2->title = fake()->title;
		$this->album2->owner_id = $this->admin->id;
		$this->album2->makeRoot();
		$this->album2->save();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresEmptyAlbums();
		parent::tearDown();
	}

	public function testMenu(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->assertViewIs('livewire.context-menus.albums-dropdown')
			->assertStatus(200);
	}

	public function testRename(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->call('renameAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.rename');
	}

	public function testMerge(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->call('mergeAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.merge');
	}

	public function testDelete(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->call('deleteAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.delete');
	}

	public function testMove(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->call('moveAll')
			->assertDispatched('closeContextMenu')
			->assertDispatched('openModal', 'forms.album.move');
	}

	public function testDownload(): void
	{
		Livewire::test(
			AlbumsDropdown::class,
			['params' => ['parentID' => null, 'albumIDs' => [$this->album1->id, $this->album2->id]]]
		)
			->call('downloadAll')
			->assertDispatched('closeContextMenu')
			->assertRedirect(route('download') . '?albumIDs=' . $this->album1->id . ',' . $this->album2->id);
	}
}
