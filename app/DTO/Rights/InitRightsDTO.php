<?php

namespace App\DTO\Rights;

use App\DTO\ArrayableDTO;

/**
 * This DTO provides the information whether some actions are available to the user.
 */
class InitRightsDTO extends ArrayableDTO
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
	public static function ofAdminIsNotRegistered(): self
	{
		return new self(
			root_album: RootAlbumRightsDTO::ofTrue(),
			settings: SettingsRightsDTO::ofTrue(),
			users: UserRightsDTO::ofTrue(),
		);
	}
}