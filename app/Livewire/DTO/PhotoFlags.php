<?php

namespace App\Livewire\DTO;

use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

/**
 * @implements Wireable<PhotoFlags>
 */
class PhotoFlags implements Wireable
{
	/** @phpstan-use UseWireable<PhotoFlags> */
	use UseWireable;

	public function __construct(
		public bool $can_autoplay,
		public bool $can_rotate,
		public bool $can_edit,
	) {
	}
}