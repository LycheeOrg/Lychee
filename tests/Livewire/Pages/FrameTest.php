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

use App\Livewire\Components\Forms\Album\Visibility;
use App\Livewire\Components\Pages\Frame;
use App\Livewire\Components\Pages\Gallery\Album;
use App\Models\Configs;
use Livewire\Livewire;
use Tests\Livewire\Base\BaseLivewireTest;

class FrameTest extends BaseLivewireTest
{
	public function testFrameLoggedOutForbidden(): void
	{
		Configs::set('mod_frame_enabled', false);
		Livewire::test(Frame::class)
			->assertForbidden();
	}

	public function testFrameLoggedInDisabled(): void
	{
		Configs::set('mod_frame_enabled', false);
		Livewire::actingAs($this->userMayUpload1)->test(Frame::class)
			->assertForbidden();
	}

	public function testFrameLoggedOutEnabled(): void
	{
		// Set up visibiltiy
		Livewire::actingAs($this->userMayUpload1)->test(Visibility::class, ['album' => $this->album1])
			->assertOk()
			->assertViewIs('livewire.forms.album.visibility')
			->toggle('is_public')
			->assertDispatched('notify', self::notifySuccess());

		$this->album1->fresh();
		$this->album1->base_class->load('access_permissions');
		$this->assertNotNull($this->album1->public_permissions());

		// Check guest can access
		Livewire::test(Album::class, ['albumId' => $this->album1->id])
			->assertStatus(200);

		// By default we are showing starred.
		Configs::set('mod_frame_enabled', true);
		Livewire::test(Frame::class)
			->assertStatus(500);

		// Show all pictures
		Configs::set('random_album_id', '');
		Configs::set('mod_frame_enabled', true);
		Livewire::test(Frame::class)
			->assertOk()
			->assertViewIs('livewire.pages.gallery.frame');

		// Check frame on album
		Livewire::test(Frame::class, ['albumId' => $this->album1->id])
			->assertOk()
			->assertViewIs('livewire.pages.gallery.frame');

		Configs::set('mod_frame_enabled', false);
		Configs::set('random_album_id', 'starred');
	}

	public function testFrameLoggedInEnabled(): void
	{
		Configs::set('mod_frame_enabled', true);
		Configs::set('random_album_id', '');
		Livewire::actingAs($this->userMayUpload1)->test(Frame::class)
			->assertOk()
			->assertViewIs('livewire.pages.gallery.frame');

		Configs::set('mod_frame_enabled', false);
		Configs::set('random_album_id', 'starred');
	}

	public function testFrameLoggedOutAlbum(): void
	{
		Configs::set('mod_frame_enabled', false);
		Livewire::test(Frame::class, ['albumId' => $this->album1->id])
			->assertForbidden();
	}

	public function testFrameLoggedInAlbum(): void
	{
		Configs::set('mod_frame_enabled', true);
		Livewire::actingAs($this->userMayUpload1)->test(Frame::class, ['albumId' => $this->album1->id])
			->assertOk()
			->assertViewIs('livewire.pages.gallery.frame');

		Configs::set('mod_frame_enabled', false);
	}
}
