<?php

namespace App\Livewire\DTO;

use App\DTO\ArrayableDTO;
use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

class PermissionsFlags extends ArrayableDTO implements Wireable
{
	use UseWireable;

	public function __construct(
		public int $user_id,
		public string $username,
		public bool $grants_full_photo_access,
		public bool $grants_download,
		public bool $grants_upload,
		public bool $grants_edit,
		public bool $grants_delete,
	) {
	}
}