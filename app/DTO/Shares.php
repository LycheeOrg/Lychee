<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * Data Transfer Object (DTO) to transmit the list of shares to the client.
 */
class Shares extends ArrayableDTO
{
	/**
	 * @param Collection $shared List of shared albums
	 * @param Collection $albums List of albums
	 * @param Collection $users  List of users
	 *
	 * @return void
	 */
	public function __construct(
		public Collection $shared,
		public Collection $albums,
		public Collection $users)
	{
	}
}
