<?php

declare(strict_types=1);

namespace App\Actions\Photo\Pipes\Standalone;

use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\PhotoCreate\StandalonePipe;
use App\DTO\PhotoCreate\StandaloneDTO;

class InitNamingStrategy implements StandalonePipe
{
	public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO
	{
		$state->namingStrategy = resolve(AbstractSizeVariantNamingStrategy::class);
		$state->namingStrategy->setPhoto($state->photo);
		$state->namingStrategy->setExtension(
			$state->sourceFile->getOriginalExtension()
		);

		return $next($state);
	}
}
