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
	protected $signature = 'lychee:variant_filesize';

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
		if ($this->confirm('This command can take tens of minutes for large instances. Do you really want to run it now ?')) {
			$variants = SizeVariant::query()
				->where('filesize', '=', 0)
				->get();

			if (count($variants) == 0) {
				$this->line('All filesize variants already set in database.');

				return false;
			}

			/* @var SizeVariant $variant */
			$this->withProgressBar($variants, function ($variant) {
				$fullPath = $variant->full_path;
				if (file_exists($fullPath)) {
					$variant->filesize = filesize($fullPath);
					if (!$variant->save()) {
						$this->line('Failed to update filesize for ' . $fullPath . '.');
					}
				} else {
					$this->line('File does not exist for ' . $fullPath . '.');
				}
			});
		}
	}
}