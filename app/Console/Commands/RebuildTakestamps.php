<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\ModelFunctions\AlbumActions\UpdateTakestamps;
use Illuminate\Console\Command;

class RebuildTakestamps extends Command
{
	/**
	 * Add color to the command line output.
	 *
	 * @var Colorize
	 */
	private $col;

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
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Colorize $colorize)
	{
		parent::__construct();

		$this->col = $colorize;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		UpdateTakestamps::reset_takestamp();
	}
}
