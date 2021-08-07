<?php

namespace App\SmartAlbums;

use App\Facades\AccessControl;
use App\Models\Photo;
use App\Relations\HasManyPhotosBySmartCondition;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	const ID = 'unsorted';
	const TITLE = 'Unsorted';

	public function __construct()
	{
		parent::__construct(self::ID, self::TITLE, false);
	}

	public static function getInstance(): self
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function photos(): HasManyPhotosBySmartCondition
	{
		return new HasManyPhotosBySmartCondition(
			$this,
			fn (Builder $q) => $q->whereNull('album_id')
		);
	}

	/**
	 * "Deletes" the album of unsorted photos.
	 *
	 * Actually, the album itself is not deleted, because it is built-in.
	 * But all photos within the album which are owned by the currnt user
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
