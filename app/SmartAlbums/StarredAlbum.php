<?php

declare(strict_types=1);

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = SmartAlbumType::STARRED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			SmartAlbumType::STARRED,
			fn (Builder $q) => $q->where('photos.is_starred', '=', true)
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
