<?php

namespace App\SmartAlbums\Utils;

use Illuminate\Support\Str;

trait MimicModel
{
	abstract public function toArray(): array;

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Convert the model instance to JSON.
	 *
	 * @param int $options
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function toJson($options = 0): string
	{
		$json = json_encode($this->jsonSerialize(), $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \RuntimeException('Could not serialize ' . get_class($this) . ': ' . json_last_error_msg());
		}

		return $json;
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
	 * @throws \InvalidArgumentException
	 */
	public function __get(string $key)
	{
		if (empty($key)) {
			throw new \InvalidArgumentException('property name must not be empty');
		}

		$studlyKey = Str::studly($key);
		$getter = 'get' . $studlyKey . 'Attribute';
		$studlyKey = lcfirst($studlyKey);

		if (method_exists($this, $getter)) {
			return $this->{$getter}();
		} elseif (property_exists($this, $studlyKey)) {
			return $this->{$studlyKey};
		} else {
			throw new \InvalidArgumentException('neither property nor getter method exist');
		}
	}

	/**
	 * Convert the model to its string representation.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}
}
