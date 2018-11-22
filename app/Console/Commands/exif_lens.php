<?php

namespace App\Console\Commands;

use App\Http\Controllers\PhotoController;
use App\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class exif_lens extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'exif_lens {from=0 : from which do we start} {nb=5 : generate exif data if missing} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get EXIF data from pictures if missing';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$argument = $this->argument('nb');
		$from = $this->argument('from');
		$timeout = $this->argument('tm');
		set_time_limit($timeout);

		// we use lens because this is the one which is most likely to be empty.
		$photos = Photo::where('lens', '=', '')->whereNotIn('lens', PhotoController::$validVideoTypes)->offset($from)->limit($argument)->get();
		if(count($photos) == 0)
		{
			$this->line('No pictures requires EXIF updates.');
			return false;
		}

		$i = $from;
		foreach ($photos as $photo){
			$url = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG').$photo->url;
			if(file_exists($url)) {
				$info = Photo::getInformations($url);
				$photo->width = $info['width'] ? $info['width'] : 0;
				$photo->height = $info['height'] ? $info['height'] : 0;
				$photo->size = $info['size'];
				$photo->iso = $info['iso'];
				$photo->aperture = $info['aperture'];
				$photo->make = $info['make'];
				$photo->model = $info['model'];
				$photo->lens = $info['lens'];
				$photo->shutter = $info['shutter'];
				$photo->focal = $info['focal'];
				$photo->takestamp = $info['takestamp'];
				if ($photo->save()) {
					$this->line($i . ': EXIF updated for ' . $photo->title);
				} else {
					$this->line($i . ': Could not get EXIF data/nothing to update for ' . $photo->title . '.');
				}
			} else {
				$this->line($i . ': File does not exists for ' . $photo->title . '.');
			}
			$i++;
		}
	}
}
