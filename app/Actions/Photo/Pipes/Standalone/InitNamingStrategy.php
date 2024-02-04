<?php

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

class InitNamingStrategy implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		$state->namingStrategy = resolve(AbstractSizeVariantNamingStrategy::class);
		$state->namingStrategy->setPhoto($state->photo);
		$state->namingStrategy->setExtension(
			$state->sourceFile->getOriginalExtension()
		);

		return $next($state);
	}
}
