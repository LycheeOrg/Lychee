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

use App\Livewire\Components\Pages\Gallery\Search;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class SearchTest extends BaseLivewireTest
{
	public function testPageLogout(): void
	{
		Livewire::test(Search::class)
			->assertRedirect();
	}

	public function testPageLoginAndBack(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class)
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->assertSet('flags.is_accessible', true)
			->assertSet('back', route('livewire-gallery'));
	}

	public function testPageLoginSearchWithoutResults(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class)
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->set('searchQuery', 'abc')
			->assertOk();
	}

	public function testPageLoginSearchWithPhotoResultsPhoto(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class)
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->set('searchQuery', $this->photo1b->title)
			->assertOk();
	}

	public function testPageLoginSearchWithPhotoResultsAlbum(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class)
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->set('searchQuery', $this->album1->title)
			->assertOk();
	}

	public function testPageLoginSearchWithPhotoResultsSubAlbum(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class, ['albumId' => $this->subAlbum1->id])
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->set('searchQuery', $this->subPhoto1->title)
			->assertSee($this->subPhoto1->id)
			->assertOk();
	}

	public function testPageLoginSearchWithoutResultsSubAlbum(): void
	{
		Livewire::actingAs($this->admin)->test(Search::class, ['albumId' => $this->subAlbum1->id])
			->assertViewIs('livewire.pages.gallery.search')
			->assertOk()
			->set('searchQuery', $this->subPhoto1->title . 'wrong')
			->assertDontSee($this->subPhoto1->id)
			->assertOk();
	}
}
