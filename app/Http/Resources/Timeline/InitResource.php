<?php

namespace App\Http\Resources\Timeline;

use App\Enum\PhotoLayoutType;
use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Initialization resource for the search.
 */
#[TypeScript()]
class InitResource extends Data
{
	public PhotoLayoutType $photo_layout;

	public function __construct()
	{
		$this->photo_layout = Configs::getValueAsEnum('timeline_photos_layout', PhotoLayoutType::class);
	}
}