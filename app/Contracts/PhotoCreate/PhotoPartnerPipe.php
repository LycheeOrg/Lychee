<?php

namespace App\Contracts\PhotoCreate;

use App\DTO\PhotoCreate\PhotoPartnerDTO;

/**
 * Basic definition of a Photo Partner pipe.
 */
interface PhotoPartnerPipe
{
	/**
	 * @param PhotoPartnerDTO                                   $state
	 * @param \Closure(PhotoPartnerDTO $state): PhotoPartnerDTO $next
	 *
	 * @return PhotoPartnerDTO
	 */
	public function handle(PhotoPartnerDTO $state, \Closure $next): PhotoPartnerDTO;
}