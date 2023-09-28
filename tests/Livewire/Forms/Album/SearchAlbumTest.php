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

namespace Tests\Livewire\Forms\Album;

use App\Livewire\Components\Forms\Album\SearchAlbum;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SearchAlbumTest extends BaseLivewireTest
{
	public function setUp(): void
	{
		parent::setUp();
		$this->album2->title = 'vzreckosowoigygagvarcknkxubsbmrczvkmwqayhsemhpwidplztkifahdywtuaghlyighdcrzxjlzzdcuj';
		$this->album2->save();
	}

	public function testSearchAlbumLoggedOut(): void
	{
		Livewire::test(SearchAlbum::class, ['parent_id' => null, 'lft' => null, 'rgt' => null])
			->assertOk()
			->assertCount('albumListSaved', 0)
			->assertSet('albumListSaved', []);

		Livewire::test(SearchAlbum::class, ['parent_id' => $this->album1->id, 'lft' => $this->album1->_lft, 'rgt' => $this->album1->_rgt])
			->assertOk()
			->assertCount('albumListSaved', 1)
			->assertSet('albumListSaved', [
				[
					'id' => null,
					'title' => __('lychee.ROOT'),
					'original' => __('lychee.ROOT'),
					'short_title' => __('lychee.ROOT'),
					'thumb' => 'img/no_images.svg',
				],
			]);
	}

	public function testSearchAlbumLoggedInAsNotOwner(): void
	{
		Livewire::actingAs($this->userMayUpload2)->test(SearchAlbum::class, ['parent_id' => null, 'lft' => null, 'rgt' => null])
			->assertOk()
			->assertCount('albumListSaved', 0)
			->assertSet('albumListSaved', []);

		Livewire::actingAs($this->userMayUpload2)->test(SearchAlbum::class, ['parent_id' => $this->album1->id, 'lft' => $this->album1->_lft, 'rgt' => $this->album1->_rgt])
			->assertOk()
			->assertCount('albumListSaved', 1)
			->assertSet('albumListSaved', [
				[
					'id' => null,
					'title' => __('lychee.ROOT'),
					'original' => __('lychee.ROOT'),
					'short_title' => __('lychee.ROOT'),
					'thumb' => 'img/no_images.svg',
				],
			]);
	}

	public function testSearchAlbumLoggedInAsOwner(): void
	{
		$shorten1 = substr($this->album1->title, 0, 3);
		$shorten2 = substr($this->album2->title, 0, 3);
		$shorten2b = substr($this->subAlbum2->title, 0, 3);

		$count = 2;

		// This is because names are randomly generated and we might get a colision
		if ($shorten2 === $shorten1) {
			$count += 2;
		} elseif ($shorten2b === $shorten1) {
			$count++;
		}

		Livewire::actingAs($this->userMayUpload1)->test(SearchAlbum::class, ['parent_id' => null, 'lft' => null, 'rgt' => null])
			->assertOk()
			->assertCount('albumListSaved', 4)
			->assertCount('albumList', 4)
			->set('search', $shorten1)
			->assertOk()
			->assertCount('albumList', $count);

		if ($shorten2b === $shorten2) {
			$count = 2;
		} else {
			$count = 1;
		}

		Livewire::actingAs($this->userMayUpload1)->test(SearchAlbum::class, ['parent_id' => $this->album1->id, 'lft' => $this->album1->_lft, 'rgt' => $this->album1->_rgt])
			->assertOk()
			->assertCount('albumListSaved', 3)
			->assertCount('albumList', 3)
			->set('search', $shorten2b)
			->assertCount('albumList', $count);
	}

	// public function testSearchAlbumLoggedNoRights(): void
	// {
	// 	Livewire::actingAs($this->userNoUpload)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
	// 		->assertForbidden();

	// 	Livewire::actingAs($this->userNoUpload)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
	// 		->assertForbidden();

	// 	Livewire::actingAs($this->userMayUpload2)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => null, Params::ALBUM_ID => $this->album1->id]])
	// 		->assertForbidden();

	// 	Livewire::actingAs($this->userMayUpload2)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
	// 		->assertForbidden();
	// }

	// public function testSearchAlbumLoggedIn(): void
	// {
	// 	Livewire::actingAs($this->userMayUpload1)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
	// 		->assertOk()
	// 		->assertViewIs('livewire.forms.album.SearchAlbum')
	// 		->call('close');

	// 	Livewire::actingAs($this->userMayUpload1)->test(SearchAlbum::class, ['params' => [Params::PARENT_ID => $this->album1->id, Params::ALBUM_ID => $this->subAlbum1->id]])
	// 		->assertOk()
	// 		->assertViewIs('livewire.forms.album.SearchAlbum')
	// 		->set('title', $this->album1->title)
	// 		->assertOk()
	// 		->call('submit')
	// 		->assertDispatched('reloadPage');
	// }
}
