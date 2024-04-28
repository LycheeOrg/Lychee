<?php

namespace App\Livewire\DTO;

use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

class OauthData implements Wireable
{
	use UseWireable;

	public function __construct(
		public string $providerType,
		public bool $isEnabled,
		public string $registrationRoute,
	) {
	}
}