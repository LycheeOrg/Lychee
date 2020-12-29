<?php

namespace App\Console\Commands;

use App\Actions\Album\UpdateTakestamps;
use App\Console\Commands\Utilities\Colorize;
use Illuminate\Console\Command;

class RebuildTakestamps extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:rebuild_albums_takestamps';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rebuild albums takestamps.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(UpdateTakestamps $updateTakestamps, Colorize $colorize)
	{
		$updateTakestamps->all();

		$this->line($colorize->green('Done.'));
	}
}
