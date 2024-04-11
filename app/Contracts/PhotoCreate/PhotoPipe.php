<?php

namespace App\Contracts\PhotoCreate;

/**
 * Basic definition of a Photo creation pipe.
 */
interface PhotoPipe
{
	/**
	 * @param PhotoDTO                            $state
	 * @param \Closure(PhotoDTO $state): PhotoDTO $next
	 *
	 * @return PhotoDTO
	 */
	public function handle(PhotoDTO $state, \Closure $next): PhotoDTO;
}