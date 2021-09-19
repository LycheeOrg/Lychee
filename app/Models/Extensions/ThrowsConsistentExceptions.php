<?php

namespace App\Models\Extensions;

use App\Exceptions\ModelDBException;

/**
 * Fixed Eloquent model for all Lychee models.
 *
 * This trait wraps some Eloquent model methods to make error handling
 * more consistent.
 * Some Eloquent methods report error conditions by using `false` as the
 * return value or throwing an exception.
 * This makes proper error handling a tedious task, because we always have
 * to check for two possible conditions and possible duplicate the error
 * handler.
 * This model unifies the error handling and also throws an exception when
 * the parent method would return `false`.
 */
trait ThrowsConsistentExceptions
{
	protected string $friendlyModelName = 'unknown model';

	/**
	 * @param array $options
	 *
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 */
	public function save(array $options = []): bool
	{
		$parentException = null;
		try {
			if (!parent::save($options)) {
				$parentException = new \RuntimeException('Eloquent\Model::save() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException) {
			throw ModelDBException::create($this->friendlyModelName, $this->wasRecentlyCreated ? 'create' : 'update', $parentException);
		}

		return true;
	}

	/**
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 */
	public function delete(): bool
	{
		$parentException = null;
		try {
			// Sic! Don't use `!$parentDelete` in condition, because we also
			// need to proceed if `$parentDelete === null` .
			// If Eloquent returns `null` (instead of `true`), this also
			// indicates a success and we must go on.
			// Eloquent, I love you .... not.
			$result = parent::delete();
			if ($result !== true && $result !== null) {
				$parentException = new \RuntimeException('Eloquent\Model::delete() returned neither returned true nor null');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException) {
			throw ModelDBException::create($this->friendlyModelName, 'delete', $parentException);
		}

		return true;
	}
}
