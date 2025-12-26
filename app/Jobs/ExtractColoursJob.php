<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Jobs;

use App\Enum\JobStatus;
use App\Exceptions\ColourPaletteExtractionException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\ColourExtractor\FarzaiExtractor;
use App\Image\ColourExtractor\LeagueExtractor;
use App\Models\Colour;
use App\Models\JobHistory;
use App\Models\Palette;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * This allows to process images on serverside while making the responses faster.
 * Note that the user will NOT see that the image is processed directly in upload when using queues.
 */
class ExtractColoursJob implements ShouldQueue
{
	use HasFailedTrait;
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	protected JobHistory $history;
	public string $photo_id;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		Photo $photo,
	) {
		$this->photo_id = $photo->id;

		// Set up our new history record.
		$this->history = new JobHistory();
		$this->history->owner_id = $photo->owner_id;
		$this->history->job = Str::limit(sprintf('Extraction Color Palette for %s [%s].', $photo->title, $this->photo_id), 200);
		$this->history->status = JobStatus::READY;

		$this->history->save();
	}

	/**
	 * Execute the job.
	 */
	public function handle(): Photo
	{
		$this->history->status = JobStatus::STARTED;
		$this->history->save();

		$photo = Photo::with(['size_variants', 'palette'])->findOrFail($this->photo_id);
		if ($photo->palette !== null || $photo->isPhoto() === false) {
			// If the photo already has a palette, we don't need to extract colours again.
			$this->history->status = JobStatus::SUCCESS;
			$this->history->save();

			return $photo;
		}

		$file = $photo->size_variants->getOriginal()->getFile();

		$config_manager = app(ConfigManager::class);
		$extractor = match ($config_manager->getValueAsString('colour_extraction_driver')) {
			'league' => new LeagueExtractor(),
			'farzai' => new FarzaiExtractor($config_manager),
			default => throw new LycheeLogicException('Unsupported colour extraction driver.'),
		};
		$colours = $extractor->extract($file);

		if (count($colours) < 5) {
			// If we don't have enough colours, we can't create a palette.
			throw new ColourPaletteExtractionException('Not enough colours extracted to create a palette.');
		}

		// Creates the colours if they don't exists yet.
		$colour_1 = Colour::fromHex($colours[0]);
		$colour_2 = Colour::fromHex($colours[1]);
		$colour_3 = Colour::fromHex($colours[2]);
		$colour_4 = Colour::fromHex($colours[3]);
		$colour_5 = Colour::fromHex($colours[4]);

		$palette = Palette::create([
			'photo_id' => $photo->id,
			'colour_1' => $colour_1->id,
			'colour_2' => $colour_2->id,
			'colour_3' => $colour_3->id,
			'colour_4' => $colour_4->id,
			'colour_5' => $colour_5->id,
		]);
		$palette->save();

		// Once the job has finished, set history status to 1.
		$this->history->status = JobStatus::SUCCESS;
		$this->history->save();

		return $photo;
	}
}