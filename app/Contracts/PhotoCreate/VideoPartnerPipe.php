<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\VideoPartnerDTO;

/**
 * Basic definition of a Video Partner pipe.
 */
interface VideoPartnerPipe
{
	/**
	 * @param VideoPartnerDTO                                   $state
	 * @param \Closure(VideoPartnerDTO $state): VideoPartnerDTO $next
	 *
	 * @return VideoPartnerDTO
	 */
	public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO;
}