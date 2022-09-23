<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Configs;
use App\SmartAlbums\Utils\Wireable;
use Illuminate\Database\Eloquent\Builder;

class StarredAlbum extends BaseSmartAlbum
{
	use Wireable;

	private static ?self $instance = null;
	public const ID = 'starred';
	public const TITLE = 'Starred';

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			self::ID,
			self::TITLE,
			Configs::getValueAsBool('public_starred'),
			fn (Builder $q) => $q->where('photos.is_starred', '=', true)
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
