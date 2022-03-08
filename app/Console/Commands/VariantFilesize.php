<?php

namespace App\Console\Commands;

use App\Models\SizeVariant;
use Illuminate\Console\Command;

class VariantFilesize extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:variant_filesize {limit=50 : number of photos to process at once (0 means all)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set filesize of size variants if missing';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		$exit_code = 0;
		$limit = intval($this->argument('limit'));

		if ($this->confirm('This command can take a long time for large instances. Do you really want to run it now ?')) {
			$variants_query = SizeVariant::query()
				->where('filesize', '=', 0)->orderBy('id');

			$count = $variants_query->count();
			if ($count == 0) {
				$this->line('All filesize variants already set in database.');

				return $exit_code;
			}

			// Internally, only holds $limit entries at once
			$variants = $variants_query->lazyById($limit);

			/* @var SizeVariant $variant */
			$this->withProgressBar($variants, function ($variant) use (&$exit_code) {
				$fullPath = $variant->getFile()->getAbsolutePath();
				if (file_exists($fullPath)) {
					$variant->filesize = filesize($fullPath);
					if (!$variant->save()) {
						$this->line('Failed to update filesize for ' . $fullPath . '.');
						$exit_code = -1;
					}
				} else {
					$this->line('No file found at ' . $fullPath . '.');
					$exit_code = -1;
				}
			});
		}

		return $exit_code;
	}
}
