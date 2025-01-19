<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Exceptions\Internal\NotImplementedException;
use Illuminate\Support\Facades\Route;

/**
 * Trait ToArrayThrowsNotImplemented.
 *
 * Now that we use Resources toArray should no longer be used.
 * Throw an exception if we encounter this function in the code.
 */
trait ToArrayThrowsNotImplemented
{
	/**
	 * @return array<string,mixed>
	 *
	 * @throws NotImplementedException
	 *
	 * @codeCoverageIgnore We should never reach this code
	 */
	final public function toArray(): array
	{
		$details = Route::getCurrentRoute()?->getName() ?? '';
		$details .= ($details !== '' ? ':' : '') . get_called_class();
		throw new NotImplementedException($details . '->toArray() is deprecated, use Resources instead.');
	}
}