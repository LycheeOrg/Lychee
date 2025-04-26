<?php

/**
 * SPDX-License-Identifier: MIT
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
			$size_variant_name = strval($this->argument('type'));
			if (!array_key_exists($size_variant_name, self::SIZE_VARIANTS)) {
				$this->error(sprintf('Type %s is not one of %s', $size_variant_name, implode(', ', array_keys(self::SIZE_VARIANTS))));

				return 1;
			}

			if (Features::active('use-s3')) {
				$this->error('This tool does not support S3 file storage.');

				return 1;
			}

			$size_variant_type = self::SIZE_VARIANTS[$size_variant_name];

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
					$size_variant_name,
					$timeout
				)
			);

			$photos = Photo::query()
				->where('type', 'like', 'image/%')
				->with('size_variants')
				->whereDoesntHave('size_variants', function (Builder $query) use ($size_variant_type): void {
					$query->where('type', '=', $size_variant_type);
				})
				->take($amount)
				->get();

			if (count($photos) === 0) {
				$this->line('No picture requires ' . $size_variant_name . '.');

				return 0;
			}

			$bar = $this->output->createProgressBar(count($photos));
			$bar->start();

			// Initialize factory for size variants
			$size_variant_factory = resolve(SizeVariantFactory::class);
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$size_variant = null;

				try {
					$size_variant_factory->init($photo);
					$size_variant = $size_variant_factory->createSizeVariantCond($size_variant_type);
				} catch (MediaFileOperationException $e) {
					$size_variant = null;
				}

				if ($size_variant !== null) {
					$this->line('   ' . $size_variant_name . ' (' . $size_variant->width . 'x' . $size_variant->height . ') for ' . $photo->title . ' created.');
				} else {
					$this->line('   Did not create ' . $size_variant_name . ' for ' . $photo->id . ' .');
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
