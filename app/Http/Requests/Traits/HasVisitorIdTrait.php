<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use Illuminate\Http\Request;

trait HasVisitorIdTrait
{
	protected ?string $visitor_id = null;

	/**
	 * @return string
	 */
	public function visitorId(): string
	{
		if ($this->visitor_id === null) {
			// Hash the ip of the the request with the user agent to create a unique visitor id
			// This is not a secure way to create a unique id, but it is good enough for our purpose.
			// For privacy reasons we don't want to store the ip or user agent in the database.
			
			/** @var Request $request */
			$request = request();

			// xxh64 is a fast hash function that is not cryptographically secure.
			// The cryptographic properties of xxh64 is actually not important to us.
			// We do not need to have a strong non-collision guarantee. (Yes, that is counter intuitive, lol)
			// Collisions are actually a "good thing" as it gives plausible deniability.
			$this->visitor_id = hash('xxh64', $request->ip() . $request->userAgent());
		}

		return $this->visitor_id;
	}
}
