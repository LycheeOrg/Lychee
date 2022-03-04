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
	 * @return mixed
	 */
	public function handle()
	{
		$limit = intval($this->argument('limit'));

		if ($this->confirm('This command can take a long time for large instances. Do you really want to run it now ?')) {
			$variants_query = SizeVariant::query()
				->where('filesize', '=', -1)->orderBy('id');

			$count = $variants_query->count();
			if ($count == 0) {
				$this->line('All filesize variants already set in database.');

				return false;
			}

			// Number of queries
			$pages = ceil($count / $limit);

			$bar = $this->output->createProgressBar($count);
			$bar->start();

			for ($i = 0; $i < $pages; $i++) {
				// No need to offset, because previous variants have no -1 filesize anymore
				$variants = $variants_query->limit($limit)->get();

				/* @var SizeVariant $variant */
				foreach ($variants as $variant) {
					$fullPath = $variant->full_path;
					if (file_exists($fullPath)) {
						$variant->filesize = filesize($fullPath);
						if (!$variant->save()) {
							$this->line('Failed to update filesize for ' . $fullPath . '.');
						}
					} else {
						$this->line('File does not exist for ' . $fullPath . '.');
					}
					$bar->advance();
				}
			}

			$bar->finish();
		}
	}
}
