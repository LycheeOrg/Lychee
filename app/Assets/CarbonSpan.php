<?php

namespace App\Assets;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Carbon;

class CarbonSpan implements Arrayable, \JsonSerializable, Jsonable
{
	protected Carbon $min;
	protected Carbon $max;

	public function __construct(Carbon $min, Carbon $max)
	{
		$this->min = $min;
		$this->max = $max;
	}

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
	 */
	public function toJson($options = 0): string
	{
		$json = json_encode($this->jsonSerialize(), $options);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new \RuntimeException('Error encoding "CarbonSpan"');
		}

		return $json;
	}

	public function toArray(): array
	{
		return [
			'min' => $this->min->toAtomString(),
			'max' => $this->min->toAtomString(),
		];
	}
}
