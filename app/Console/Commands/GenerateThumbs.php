<?php

namespace App\Console\Commands;

use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class GenerateThumbs extends Command
{
	/**
	 * @var array
	 */
	const SIZE_VARIANTS = [
		'small' => SizeVariant::SMALL,
		'small2x' => SizeVariant::SMALL2X,
		'medium' => SizeVariant::MEDIUM,
		'medium2x' => SizeVariant::MEDIUM2X,
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
	 */
	public function handle(): int
	{
		$sizeVariantName = $this->argument('type');
		if (!array_key_exists($sizeVariantName, self::SIZE_VARIANTS)) {
			$this->error(sprintf('Type %s is not one of %s', $sizeVariantName, implode(', ', array_flip(self::SIZE_VARIANTS))));

			return 1;
		}
		$sizeVariantID = self::SIZE_VARIANTS[$sizeVariantName];

		set_time_limit($this->argument('timeout'));

		$this->line(
			sprintf(
				'Will attempt to generate up to %s %s images with a timeout of %d seconds...',
				$this->argument('amount'),
				$sizeVariantName,
				$this->argument('timeout')
			)
		);

		$photos = Photo::query()
			->where('type', 'like', 'image/%')
			->whereDoesntHave('size_variants_raw', function (Builder $query) use ($sizeVariantID) {
				$query->where('size_variant', '=', $sizeVariantID);
			})
			->take($this->argument('amount'))
			->get();

		if (count($photos) == 0) {
			$this->line('No picture requires ' . $sizeVariantName . '.');

			return 0;
		}

		$bar = $this->output->createProgressBar(count($photos));
		$bar->start();

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			$origSizeVariant = $photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
			$origFullPath = $origSizeVariant->full_path;
			// Initialize factory for size variants
			$sourceFileInfo = new SourceFileInfo(
				pathinfo($origFullPath, PATHINFO_BASENAME),
				$photo->type,
				$origFullPath
			);
			/** @var SizeVariantNamingStrategy $namingStrategy */
			$namingStrategy = resolve(SizeVariantNamingStrategy::class);
			$namingStrategy->setSourceFileInfo($sourceFileInfo);
			/** @var SizeVariantFactory $sizeVariantFactory */
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			$sizeVariantFactory->init($photo, $namingStrategy);

			$sizeVariant = $sizeVariantFactory->createSizeVariantCond($sizeVariantID);

			if ($sizeVariant) {
				$this->line('   ' . $sizeVariantName . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
			} else {
				$this->line('   Could not create ' . $sizeVariantName . ' for ' . $photo->title . ' (' . $photo->width . 'x' . $photo->height . ').');
			}
			$bar->advance();
		}

		$bar->finish();
		$this->line('  ');
	}
}
