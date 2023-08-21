<?php

namespace App\Models\Extensions;

use App\Exceptions\Internal\NotImplementedException;
use Illuminate\Support\Facades\Route;

/**
 * Trait ToArrayThrowsNotImplemented.
 *
 * Now that we use Resources toArray should no longer be used.
 * Throw an exception if we encounter this function in the code.
 *
 * Because Livewire uses toArray to serialize models when passing them to sub components,
 * we still need to allow those cases. Those can be detected by the Route::is() call
 */
trait ToArrayThrowsNotImplemented
{
	abstract protected function _toArray(): array;

	final public function toArray(): array
	{
		if (Route::is('livewire_index') || Route::is('livewire.message')) {
			return $this->_toArray();
		}
		$details = Route::getCurrentRoute()?->getName() ?? '';
		$details .= ($details !== '' ? ':' : '') . get_called_class();
		throw new NotImplementedException($details . '->toArray() is deprecated, use Resources instead.');
	}
}