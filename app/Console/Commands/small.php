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
	protected $signature = 'small {nb=5 : generate small pictures if missing} {tm=600 : timeout time requirement}';

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

		$this->line('Will attempt to generate up to '.$argument.' small ('.Configs::get_value('small_max_width').'x'.Configs::get_value('small_max_height').') images with a timeout of '.$timeout.' seconds...');
		$photos = Photo::where('small', '=', '')->where('type', 'like', 'image/%')->get();
		if (count($photos) == 0) {
			$this->line('No picture requires small.');
			return false;
		}

		$count = 0;
		foreach ($photos as $photo) {
			$resWidth = 0;
			$resHeight = 0;
			if ($this->photoFunctions->createMedium(
				$photo,
				intval(Configs::get_value('small_max_width')),
				intval(Configs::get_value('small_max_height')),
				$resWidth, $resHeight, false, 'SMALL')
			) {
				$photo->small = $resWidth . 'x' . $resHeight;
				$photo->save();
				$this->line('small ('.$photo->small.') for '.$photo->title.' created.');
				$count++;
				if ($count == $argument) {
					$this->line('Rerun this command to check for more images.');
					break;
				}
			}
			else {
				$this->line('Could not create small for '.$photo->title.' ('.$photo->width.'x'.$photo->height.').');
			}
		}
	}
}
