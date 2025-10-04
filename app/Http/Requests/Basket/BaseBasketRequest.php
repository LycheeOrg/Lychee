<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Basket;

use App\Contracts\Http\Requests\HasBasket;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasBasketTrait;

abstract class BaseBasketRequest extends BaseApiRequest implements HasBasket
{
	use HasBasketTrait;

	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$this->prepareBasket();
	}
}
