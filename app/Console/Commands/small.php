<?php

namespace App\Console\Commands;

use App\Configs;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;

class small extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'small {nb=5 : generate medium pictures if missing} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create small pictures if missing';

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$argument = $this->argument('nb');
		$timeout = $this->argument('tm');
		set_time_limit($timeout);

		$photos = Photo::where('small', '=', 0)->limit($argument)->get();
		if (count($photos) == 0) {
			$this->line('No pictures requires small.');
			return false;
		}

		foreach ($photos as $photo) {
			if ($this->photoFunctions->createMedium(
				$photo,
				intval(Configs::get_value('small_max_width')),
				intval(Configs::get_value('small_max_height')), 'SMALL')
			) {
				$photo->small = 1;
				$photo->save();
				$this->line('small for '.$photo->title.' created.');
			}
			else {
				$this->line('Could not create small for '.$photo->title.'.');
			}
		}
	}
}
