<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = 'unsorted';

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	public function __construct()
	{
		parent::__construct(
			self::ID,
			__('lychee.UNSORTED') ?? 'lychee.UNSORTED',
			fn (Builder $q) => $q->whereNull('photos.album_id')
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
