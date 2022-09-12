<?php

namespace App\DTO;

class AlbumProtectionPolicy extends DTO
{
	public function __construct(
		public bool $is_public,
		public bool $requires_link,
		public bool $is_nsfw,
		public bool $is_downloadable,
		public bool $is_share_button_visible,
		public bool $grants_full_photo
	) {
	}
}
