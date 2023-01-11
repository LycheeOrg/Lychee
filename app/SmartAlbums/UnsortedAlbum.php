<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Facades\Lang;
use App\SmartAlbums\Utils\Wireable;
use Illuminate\Database\Eloquent\Builder;

class UnsortedAlbum extends BaseSmartAlbum
{
	use Wireable;

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
			Lang::get('UNSORTED'),
			false,
			fn (Builder $q) => $q->whereNull('photos.album_id')
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
