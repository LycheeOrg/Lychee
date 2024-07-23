<?php

namespace App\Http\Resources\Models;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class ThumbResource extends Data
{
	public string $id;
	public string $type;
	public ?string $thumb;
	public ?string $thumb2x;

	public function __construct(string $id, string $type, string $thumbUrl, ?string $thumb2xUrl = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumb = $thumbUrl;
		$this->thumb2x = $thumb2xUrl;
	}

	/**
	 * @param string|null $id
	 * @param string|null $type
	 * @param string|null $thumbUrl
	 * @param string|null $thumb2xUrl
	 *
	 * @return ($id is null ? null : ThumbResource)
	 */
	public static function make(?string $id, ?string $type, ?string $thumbUrl, ?string $thumb2xUrl = null): ?self
	{
		if ($id === null) {
			return null;
		}

		/** @var string $id */
		/** @var string $type */
		/** @var string $thumbUrl */
		return new self($id, $type, $thumbUrl, $thumb2xUrl);
	}
}
