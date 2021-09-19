<?php

namespace App\SmartAlbums;

use App\Models\Extensions\UTCBasedTimes;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;

/**
 * Class FakeModel.
 *
 * This class mimics some behaviour of
 * {@link \Illuminate\Database\Eloquent\Model}.
 * The main difference is that a fake model does not actually exist on the
 * DB layer and thus cannot be loaded, saved, refreshed, etc.
 * However, it enables to use the smart albums which extend
 * {@link \App\SmartAlbums\BaseSmartAlbum} as if they were real models.
 * In particular, they can have relationships with photos.
 */
abstract class FakeModel implements Arrayable, \JsonSerializable, Jsonable
{
	use HasAttributes;
	use HidesAttributes;
	use HasSimpleRelationships;
	use UTCBasedTimes {
		UTCBasedTimes::serializeDate insteadof HasAttributes;
		UTCBasedTimes::fromDateTime insteadof HasAttributes;
		UTCBasedTimes::asDateTime insteadof HasAttributes;
	}

	/**
	 * Serializes this object into an array.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::toArray()}.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return array_merge($this->attributesToArray(), $this->relationsToArray());
	}

	/**
	 * Serializes this object into an array.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::jsonSerialize()}.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @see BaseSmartAlbum::toArray()
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Convert the model instance to JSON.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::toJson()}.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws JsonEncodingException
	 */
	public function toJson($options = 0): string
	{
		$json = json_encode($this->jsonSerialize(), $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw JsonEncodingException::forModel($this, json_last_error_msg());
		}

		return $json;
	}

	/**
	 * "Deletes" a fake model.
	 *
	 * As a fake model does not actually exist on the DB layer,
	 * it is a logical programming error to do so.
	 * Hence, this implementation always returns false.
	 *
	 * @return bool true on success
	 */
	public function delete(): bool
	{
		return false;
	}

	/**
	 * Get the key type.
	 *
	 * Always returns `string`, because smart albums use strings as key.
	 *
	 * @return string
	 */
	public function getKeyType(): string
	{
		return 'string';
	}

	/**
	 * Get the value indicating whether the IDs are incrementing.
	 *
	 * Always returns `false`.
	 *
	 * @return bool
	 */
	public function getIncrementing(): bool
	{
		return false;
	}

	/**
	 * Get the name of primary key for the model.
	 *
	 * Always returns 'id'.
	 *
	 * @return string
	 */
	public function getKeyName(): string
	{
		return 'id';
	}

	/**
	 * Get the value of the model's primary key.
	 *
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->attributes['id'];
	}

	/**
	 * Determine if the model uses timestamps.
	 *
	 * Always returns `false`.
	 *
	 * @return bool
	 */
	public function usesTimestamps(): bool
	{
		return false;
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__get()}.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws InvalidCastException
	 */
	public function __get(string $key)
	{
		return $this->getAttribute($key);
	}

	/**
	 * Dynamically set attributes on the model.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__set()}.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws InvalidTimeZoneException
	 */
	public function __set(string $key, $value)
	{
		$this->setAttribute($key, $value);
	}

	/**
	 * Determine if the given attribute exists.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::offsetExists()}.
	 *
	 * @param string $offset
	 *
	 * @return bool
	 *
	 * @throws InvalidCastException
	 */
	public function offsetExists(string $offset): bool
	{
		return !is_null($this->getAttribute($offset));
	}

	/**
	 * Get the value for a given offset.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::offsetGet()}.
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 *
	 * @throws InvalidCastException
	 */
	public function offsetGet(string $offset)
	{
		return $this->getAttribute($offset);
	}

	/**
	 * Set the value for a given offset.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::offsetSet()}.
	 *
	 * @param string $offset
	 * @param mixed  $value
	 *
	 * @return void
	 *
	 * @throws InvalidCastException
	 * @throws JsonEncodingException
	 * @throws InvalidTimeZoneException
	 */
	public function offsetSet(string $offset, $value): void
	{
		$this->setAttribute($offset, $value);
	}

	/**
	 * Unset the value for a given offset.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::offsetUnset()}.
	 *
	 * @param string $offset
	 *
	 * @return void
	 */
	public function offsetUnset(string $offset): void
	{
		unset($this->attributes[$offset], $this->relations[$offset]);
	}

	/**
	 * Determine if an attribute or relation exists on the model.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__isset()}.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @throws InvalidCastException
	 */
	public function __isset(string $key): bool
	{
		return $this->offsetExists($key);
	}

	/**
	 * Unset an attribute on the model.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__unset()}.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function __unset(string $key): void
	{
		$this->offsetUnset($key);
	}

	/**
	 * Handle dynamic method calls into the model.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__call()}.
	 *
	 * @param string $method
	 * @param array  $parameters
	 *
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call(string $method, array $parameters)
	{
		if (in_array($method, ['increment', 'decrement'])) {
			return $this->$method(...$parameters);
		}

		if ($resolver = (static::$relationResolvers[get_class($this)][$method] ?? null)) {
			return $resolver($this);
		}

		throw new \BadMethodCallException('Method ' . $method . ' not defined on ' . get_class($this));
	}

	/**
	 * Convert the model to its string representation.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__toString()}.
	 *
	 * @return string
	 *
	 * @throws JsonEncodingException
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}

	/**
	 * Prepare the object for serialization.
	 *
	 * Copied and pasted from {@link \Illuminate\Database\Eloquent\Model::__sleep()}.
	 *
	 * @return array
	 */
	public function __sleep(): array
	{
		$this->mergeAttributesFromClassCasts();

		$this->classCastCache = [];

		return array_keys(get_object_vars($this));
	}
}
