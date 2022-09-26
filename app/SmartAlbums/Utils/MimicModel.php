<?php

namespace App\SmartAlbums\Utils;

use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Str;

trait MimicModel
{
	abstract public function toArray(): array;

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @throws \JsonException
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
			return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			throw new JsonEncodingException('Error encoding [' . get_class($this) . '] to JSON', 0, $e);
		}
	}

	/**
	 * Gets a property dynamically.
	 *
	 * This method is inspired by
	 * {@link \Illuminate\Database\Eloquent\Model::__get()}
	 * and enables the using class to be treated the same way as real models.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws InternalLycheeException
	 */
	public function __get(string $key)
	{
		if ($key === '') {
			throw new LycheeInvalidArgumentException('property name must not be empty');
		}

		$studlyKey = Str::studly($key);
		$getter = 'get' . $studlyKey . 'Attribute';
		$studlyKey = lcfirst($studlyKey);

		if (method_exists($this, $getter)) {
			return $this->{$getter}(); // @phpstan-ignore-line, PhpStan does not like variadic calls
		} elseif (property_exists($this, $key)) {
			return $this->{$key}; // @phpstan-ignore-line, PhpStan does not like variadic calls
		} elseif (property_exists($this, $studlyKey)) {
			return $this->{$studlyKey}; // @phpstan-ignore-line, PhpStan does not like variadic calls
		} else {
			throw new LycheeInvalidArgumentException('neither property nor getter method exist for [' . $getter . '/' . $key . '/' . $studlyKey . ']');
		}
	}

	/**
	 * Convert the model to its string representation.
	 *
	 * @return string
	 *
	 * @throws JsonEncodingException
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}
}
