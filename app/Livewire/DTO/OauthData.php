<?php

namespace App\Livewire\DTO;

use App\Livewire\Traits\UseWireable;
use Livewire\Wireable;

/**
 * @implements Wireable<OauthData>
 */
class OauthData implements Wireable
{
	/** @phpstan-use UseWireable<OauthData> */
	use UseWireable;

	public function __construct(
		public string $providerType,
		public bool $isEnabled,
		public string $registrationRoute,
	) {
	}
}