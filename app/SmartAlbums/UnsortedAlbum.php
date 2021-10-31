<?php

namespace App\SmartAlbums;

use App\Facades\AccessControl;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'unsorted';
	const TITLE = 'Unsorted';

	public function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			false,
			fn (Builder $q) => $q->whereNull('photos.album_id')
		);
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		// The following two lines are only needed due to testing.
		// The same instance of this class is used for all tests, because
		// the singleton stays alive during tests.
		// This implies that the relation of photos is never be reloaded
		// but remains constant during all tests (it equals the empty set)
		// and the tests fails.
		unset(self::$instance->photos);
		unset(self::$instance->thumb);

		return self::$instance;
	}

	/**
	 * "Deletes" the album of unsorted photos.
	 *
	 * Actually, the album itself is not deleted, because it is built-in.
	 * But all photos within the album which are owned by the current user
	 * are deleted.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$success = true;

		$photos = $this->photos()
			->where('owner_id', '=', AccessControl::id())
			->get();
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			// This also takes care of proper deletion of physical files from disk
			$success &= $photo->delete();
		}

		return $success;
	}
}
