<?php

namespace App\DTO\Rights;

use App\DTO\ArrayableDTO;

/**
 * This DTO provides the application rights of the user.
 */
class GlobalRightsDTO extends ArrayableDTO
{
	public function __construct(
		public RootAlbumRightsDTO $root_album,
		public SettingsRightsDTO $settings,
		public UserRightsDTO $users
	) {
	}

	/**
	 * Create from current user.
	 *
	 * @return self
	 */
	public static function ofCurrentUser(): self
	{
		return new self(
			root_album: RootAlbumRightsDTO::ofCurrentUser(),
			settings: SettingsRightsDTO::ofCurrentUser(),
			users: UserRightsDTO::ofCurrentUser()
		);
	}

	/**
	 * Create from no admin registerd.
	 *
	 * @return self
	 */
	public static function ofUnregisteredAdmin(): self
	{
		return new self(
			root_album: RootAlbumRightsDTO::ofUnregisteredAdmin(),
			settings: SettingsRightsDTO::ofUnregisteredAdmin(),
			users: UserRightsDTO::ofUnregisteredAdmin(),
		);
	}
}