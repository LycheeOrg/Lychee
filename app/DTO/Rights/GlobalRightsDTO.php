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
		public UserManagementRightsDTO $user_management,
		public UserRightsDTO $user,
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
			user_management: UserManagementRightsDTO::ofCurrentUser(),
			user: UserRightsDTO::ofCurrentUser(),
		);
	}
}