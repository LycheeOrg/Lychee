<?php

namespace App\Http\Resources\Search;

use App\Models\Configs;
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
class InitResource extends Data
{
	public int $search_minimum_length = 3;

	public function __construct()
	{
		$this->search_minimum_length = Configs::getValueAsInt('search_minimum_length_required');
	}
}