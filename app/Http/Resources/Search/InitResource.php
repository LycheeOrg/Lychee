<?php

namespace App\Http\Resources\Search;

use App\Models\Configs;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Initialization resource for the search.
 */
#[TypeScript()]
class InitResource extends Data
{
	public int $search_minimum_length = 3;

	public function __construct()
	{
		$this->search_minimum_length = Configs::getValueAsInt('search_minimum_length_required');
	}
}