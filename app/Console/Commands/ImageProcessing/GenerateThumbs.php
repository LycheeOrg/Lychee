<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Assets\Features;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\SizeVariantFactory;
use App\Enum\SizeVariantType;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\UnexpectedException;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class GenerateThumbs extends Command
{
	/**
	 * @var array<string,SizeVariantType>
	 */
	public const SIZE_VARIANTS = [
		'placeholder' => SizeVariantType::PLACEHOLDER,
		'thumb' => SizeVariantType::THUMB,
		'thumb2x' => SizeVariantType::THUMB2X,
		'small' => SizeVariantType::SMALL,
		'small2x' => SizeVariantType::SMALL2X,
		'medium' => SizeVariantType::MEDIUM,
		'medium2x' => SizeVariantType::MEDIUM2X,
	];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:generate_thumbs {type : thumb name} {amount=100 : amount of photos to process} {timeout=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate intermediate thumbs if missing';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			$sizeVariantName = strval($this->argument('type'));
			if (!array_key_exists($sizeVariantName, self::SIZE_VARIANTS)) {
				$this->error(sprintf('Type %s is not one of %s', $sizeVariantName, implode(', ', array_keys(self::SIZE_VARIANTS))));

				return 1;
			}

			if (Features::active('use-s3')) {
				$this->error('This tool does not support S3 file storage.');

				return 1;
			}

			$sizeVariantType = self::SIZE_VARIANTS[$sizeVariantName];

			$amount = (int) $this->argument('amount');
			$timeout = (int) $this->argument('timeout');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$this->line(
				sprintf(
					'Will attempt to generate up to %d %s images with a timeout of %d seconds...',
					$amount,
					$sizeVariantName,
					$timeout
				)
			);

			$photos = Photo::query()
				->where('type', 'like', 'image/%')
				->with('size_variants')
				->whereDoesntHave('size_variants', function (Builder $query) use ($sizeVariantType) {
					$query->where('type', '=', $sizeVariantType);
				})
				->take($amount)
				->get();

			if (count($photos) === 0) {
				$this->line('No picture requires ' . $sizeVariantName . '.');

				return 0;
			}

			$bar = $this->output->createProgressBar(count($photos));
			$bar->start();

			// Initialize factory for size variants
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$sizeVariant = null;

				try {
					$sizeVariantFactory->init($photo);
					$sizeVariant = $sizeVariantFactory->createSizeVariantCond($sizeVariantType);
				} catch (MediaFileOperationException $e) {
					$sizeVariant = null;
				}

				if ($sizeVariant !== null) {
					$this->line('   ' . $sizeVariantName . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
				} else {
					$this->line('   Did not create ' . $sizeVariantName . ' for ' . $photo->id . ' .');
				}
				$bar->advance();
			}

			$bar->finish();
			$this->line('  ');

			return 0;
		} catch (LycheeException|SymfonyConsoleException $e) {
			if ($e instanceof ExternalLycheeException) {
				throw $e;
			} else {
				throw new UnexpectedException($e);
			}
		}
	}
}
