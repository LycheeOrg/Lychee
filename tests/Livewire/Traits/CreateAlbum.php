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

namespace Tests\Livewire\Traits;

use App\Models\Album;
use App\Models\User;

/**
 * @property User $admin
 */
trait CreateAlbum
{
	protected function createAlbum(?int $ower_id = null): Album
	{
		$album = new Album();
		$album->title = fake()->title();
		$album->owner_id = $ower_id ?? $this->admin->id;
		$album->makeRoot();
		$album->save();

		return $album;
	}
}
