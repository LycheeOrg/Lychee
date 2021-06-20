<?php

namespace App\Models\Extensions;

use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Thumb implements Arrayable, JsonSerializable
{
	protected int $id;
	protected string $type;
	protected ?string $thumbUrl;
	protected ?string $thumb2xUrl;

	public function __construct(int $id, string $type, ?string $thumbUrl, ?string $thumb2xUrl = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumbUrl = $thumbUrl;
		$this->thumb2xUrl = $thumb2xUrl;
	}

	public static function createFromPhoto(?Photo $photo): ?Thumb
	{
		if (!$photo) {
			return null;
		}
		$thumb = $photo->size_variants->getSizeVariant(SizeVariant::THUMB);
		$thumb2x = $photo->size_variants->getSizeVariant(SizeVariant::THUMB2X);

		return new self(
			$photo->id,
			$photo->type,
			$thumb ? $thumb->url : null,
			$thumb2x ? $thumb2x->url : null
		);
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'thumb' => $this->thumbUrl,
			'thumb2x' => $this->thumb2xUrl,
		];
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @see SizeVariants::toArray()
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
