<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\Models\ConfigResource;
use App\Models\Configs;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Data Transfer Object (DTO) to transmit the top albums to the client.
 *
 * This DTO differentiates between albums which are owned by the user and
 * "shared" albums which the user does not own, but is allowed to see.
 * The term "shared album" might be a little misleading here.
 * Albums which are owned by the user himself may also be shared (with
 * other users.)
 * Actually, in this context "shared albums" means "foreign albums".
 */
#[TypeScript()]
class ConfigCollectionResource extends Data
{
	/** @var array<string,ConfigResource[]> */
	public array $configs;

	/**
	 * @param Collection<int,Configs> $configs
	 *
	 * @return void
	 */
	public function __construct(Collection $configs)
	{
		$configs
			// Group by category
			->chunkWhile(fn (Configs $value, int $key, Collection $chunk) => $value->cat === $chunk->last()->cat)
			// For each category, map the configs to ConfigResource
			->each(function (Collection $chunk) {
				$configs_data = ConfigResource::collect($chunk->all());
				$this->configs[$chunk->first()->cat] = $configs_data;
			});
	}
}