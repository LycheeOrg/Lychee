<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Contracts\Http\Requests;

interface HasHeaderFocus
{
	/**
	 * @return array{x:string,y:string}|null
	 */
	public function headerPhotoFocus(): ?array;
}
