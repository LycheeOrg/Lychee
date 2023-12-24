<?php

namespace App\Http\Resources\Models;

use App\Models\SizeVariant;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Size variant conversions.
 * Supports a noUrl flag which will remove the url on output.
 */
class SizeVariantResource extends JsonResource
{
	private bool $noUrl = false;

	public function __construct(SizeVariant $sizeVariant)
	{
		parent::__construct($sizeVariant);
	}

	/**
	 * Set noUrl in flow mode (operations can be chained after).
	 *
	 * @param bool $noUrl
	 *
	 * @return SizeVariantResource
	 */
	public function setNoUrl(bool $noUrl): self
	{
		$this->noUrl = $noUrl;

		return $this;
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'type' => $this->resource->type,
			'filesize' => $this->resource->filesize,
			'height' => $this->resource->height,
			'width' => $this->resource->width,
			'url' => $this->when(!$this->noUrl, $this->resource->url),
		];
	}
}
