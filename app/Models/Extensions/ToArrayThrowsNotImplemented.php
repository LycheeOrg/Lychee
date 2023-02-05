<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\NotImplementedException;

/**
 * Trait ToArrayThrowsNotImplemented.
 *
 * Now that we use Resources toArray should no longer be used.
 * Throw an exception if we encounter this function in the code.
 */
trait ToArrayThrowsNotImplemented
{
	final public function toArray(): array
	{
		throw new NotImplementedException('->toArray() is deprecated, use Resources instead.');
	}
}