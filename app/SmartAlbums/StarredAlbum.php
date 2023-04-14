<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = 'starred';

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			self::ID,
			__('lychee.STARRED') ?? 'lychee.STARRED',
			fn (Builder $q) => $q->where('photos.is_starred', '=', true)
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
