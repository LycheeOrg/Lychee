<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

trait HasHeaderFocusTrait
{
	/**
	 * @var array{x:string,y:string}|null
	 */
	protected ?array $header_photo_focus = null;

	/**
	 * @return array{x:string,y:string}|null
	 */
	public function headerPhotoFocus(): ?array
	{
		return $this->header_photo_focus;
	}
}
