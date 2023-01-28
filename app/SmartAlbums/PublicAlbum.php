<?php

namespace App\SmartAlbums;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Smart built-in album "Public".
 *
 * The Public album lists all photos which are explicitly made public.
 * The album des not include photos which are public due to being part of
 * a public album.
 * This behaviour is intended due to the following reasons:
 *
 *  1. If all photos (including those of public albums) were included, the
 *     album would become huge and unusable.
 *     Especially, the load time would be HUGE even for mid-size
 *     installations.
 *  2. The whole purpose of the smart album is to easily spot public photos
 *     which are accidentally public, but are not meant to be public.
 *     While public albums can be easily found, this is not true for photos
 *     which are made public individually.
 */
class PublicAlbum extends BaseSmartAlbum
{
	private static ?self $instance = null;
	public const ID = 'public';

	/**
	 * Constructor.
	 *
	 * Note that the condition only includes photos which are explicitly made
	 * public, but does not include photos which are public due to being part
	 * of a public album.
	 * **This is intended behaviour!**
	 * See description of the whole class {@link PublicAlbum} for an
	 * explanation.
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			self::ID,
			__('lychee.PUBLIC'),
			false,
			fn (Builder $q) => $q->where('photos.is_public', '=', true)
		);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}
}
