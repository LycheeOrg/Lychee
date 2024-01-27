<?php

namespace App\Actions\Photo\Pipes;

use App\Actions\Photo\Strategies\AddDuplicateStrategy;
use App\Actions\Photo\Strategies\AddPhotoPartnerStrategy;
use App\Actions\Photo\Strategies\AddStandaloneStrategy;
use App\Actions\Photo\Strategies\AddVideoPartnerStrategy;
use App\Contracts\PhotoCreatePipe;
use App\DTO\PhotoCreateDTO;

/**
 * Assert wether we support said file.
 */
class Process implements PhotoCreatePipe
{
	public function handle(PhotoCreateDTO $state, \Closure $next): PhotoCreateDTO
	{
		/*
		 * From here we need to use a strategy depending on whether we have
		 *
		 *  - a duplicate
		 *  - a "stand-alone" media file (i.e. a photo or video without a partner)
		 *  - a photo which is the partner of an already existing video
		 *  - a video which is the partner of an already existing photo
		 */
		if ($state->duplicate !== null) {
			$strategy = new AddDuplicateStrategy($state->strategyParameters, $state->duplicate);
		} else {
			if ($state->livePartner === null) {
				$strategy = new AddStandaloneStrategy($state->strategyParameters, $state->sourceFile);
			} else {
				if ($state->sourceFile->isSupportedVideo()) {
					$strategy = new AddVideoPartnerStrategy($state->strategyParameters, $state->sourceFile, $state->livePartner);
				} elseif ($state->sourceFile->isSupportedImage()) {
					$strategy = new AddPhotoPartnerStrategy($state->strategyParameters, $state->sourceFile, $state->livePartner);
				} else {
					// Accepted, but unsupported raw files are added as stand-alone files
					$strategy = new AddStandaloneStrategy($state->strategyParameters, $state->sourceFile);
				}
			}
		}

		$state->photo = $strategy->do();

		return $next($state);
	}
}