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

namespace Tests\Livewire\Forms\Photo;

use App\Contracts\Livewire\Params;
use App\Livewire\Components\Forms\Photo\Tag;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class TagTest extends BaseLivewireTest
{
	public function testTagLoggedOut(): void
	{
		Livewire::test(Tag::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testTagLoggedNoRights(): void
	{
		Livewire::actingAs($this->userNoUpload)->test(Tag::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userNoUpload)->test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Tag::class, ['params' => [Params::ALBUM_ID => null, Params::PHOTO_ID => $this->photo1->id]])
			->assertForbidden();

		Livewire::actingAs($this->userMayUpload2)->test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertForbidden();
	}

	public function testTagLoggedIn(): void
	{
		Livewire::actingAs($this->userMayUpload1)->test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.tag')
			->call('close');

		Livewire::actingAs($this->userMayUpload1)->test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.tag')
			->set('tag', $this->album1->title)
			->assertOk()
			->call('submit')
			->assertDispatched('reloadPage');

		Livewire::actingAs($this->userMayUpload1)->test(Tag::class, ['params' => [Params::ALBUM_ID => $this->album1->id, Params::PHOTO_ID => $this->subPhoto1->id]])
			->assertOk()
			->assertViewIs('livewire.forms.photo.tag')
			->set('tag', $this->album2->title)
			->set('shall_override', true)
			->assertOk()
			->call('submit')
			->assertDispatched('reloadPage');
	}
}
