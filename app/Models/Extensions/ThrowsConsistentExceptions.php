<?php

namespace App\Models\Extensions;

use App\Exceptions\ModelDBException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Str;
use function Safe\json_encode;

/**
 * Fixed Eloquent model for all Lychee models.
 *
 * This trait wraps some Eloquent model methods to make error handling
 * more consistent.
 * Some Eloquent methods report error conditions by using a mix of returning
 * `false` or throwing an exception.
 * This makes proper error handling a tedious task, because we always have
 * to check for two possible conditions.
 * This model unifies error reporting and also throws an exception when
 * the original parent method would return `false`.
 */
trait ThrowsConsistentExceptions
{
	protected function friendlyModelName(): string
	{
		$name = Str::snake(class_basename($this), ' ');
		// Remove some typical, implementation-specific pre- and suffixes from the name
		return str_replace('/(^abstract )|( impl$)|( interface$)/', '', $name);
	}

	/**
	 * Converts the instance into an (associative) array.
	 *
	 * @internal Note, that this method must not declare a return type.
	 *           The signature of this method must be compatible to
	 *           {@link \Illuminate\Database\Eloquent\Model::toArray()} which
	 *           neither declares a return type.
	 *           Otherwise, PHP will fail with a fatal parsing error.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public function toArray();

	/**
	 * @param array $options
	 *
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 *
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function save(array $options = []): bool
	{
		$parentException = null;
		try {
			// Note, `Model::save` may also return `null` which also indicates a success
			if (parent::save($options) === false) {
				$parentException = new \RuntimeException('Eloquent\Model::save() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException !== null) {
			throw ModelDBException::create($this->friendlyModelName(), $this->wasRecentlyCreated ? 'creating' : 'updating', $parentException);
		}

		return true;
	}

	/**
	 * @return bool always return true
	 *
	 * @throws ModelDBException thrown on failure
	 *
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function delete(): bool
	{
		$parentException = null;
		try {
			// Sic! Don't use `!$parentDelete` in condition, because we also
			// need to proceed if `$parentDelete === null` .
			// If Eloquent returns `null` (instead of `true`), this also
			// indicates a success, and we must go on.
			// Eloquent, I love you .... not.
			$result = parent::delete();
			if ($result === false) {
				$parentException = new \RuntimeException('Eloquent\Model::delete() returned false');
			}
		} catch (\Throwable $e) {
			$parentException = $e;
		}
		if ($parentException !== null) {
			throw ModelDBException::create($this->friendlyModelName(), 'deleting', $parentException);
		}

		return true;
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @throws \JsonException
	 *
	 * @see ThrowsConsistentExceptions::toArray()
	 */
	public function jsonSerialize(): array
	{
		try {
			return $this->toArray();
		} catch (\Exception $e) {
			throw new \JsonException(get_class($this) . '::toArray() failed', 0, $e);
		}
	}

	/**
	 * Convert the model instance to JSON.
	 *
	 * The error message is inspired by {@link JsonEncodingException::forModel()}.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws JsonEncodingException
	 */
	public function toJson($options = 0): string
	{
		try {
			// Note, we must not use the option `JSON_THROW_ON_ERROR` here,
			// because this does not clear `json_last_error()` from any
			// previous, stalled error message.
			// But `\Illuminate\Http\JsonResponse::setData()` falsy assumes
			// that this method does so.
			// Hence, we call `json_encode` _without_ specifying
			// `JSON_THROW_ON_ERROR` and then mimic that behaviour.
			// TODO: VERIFY THIS
			$json = json_encode($this->jsonSerialize(), $options);
			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \JsonException(json_last_error_msg(), json_last_error());
			}

			return $json;
		} catch (\JsonException $e) {
			throw new JsonEncodingException('Error encoding model [' . get_class($this) . '] to JSON', 0, $e);
		}
	}
}
