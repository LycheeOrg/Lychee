<?php

namespace App\Http\Resources\GalleryConfigs;

use App\Enum\MapProviders;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class MapProviderData extends Data
{
	public string $layer;
	public string $attribution;

	public function __construct()
	{
		$map_providers = Configs::getValueAsEnum('map_provider', MapProviders::class);
		$this->attribution = $map_providers->getAtributionHtml();
		$this->layer = $map_providers->getLayer();
	}
}