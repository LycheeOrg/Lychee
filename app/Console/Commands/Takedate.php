<?php

namespace App\Console\Commands;

use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Storage;

class Takedate extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:takedate {from=0 : from which do we start} {nb=5 : generate exif data if missing} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Make sure takedate is correct';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(Extractor $metadataExtractor)
	{
		$argument = $this->argument('nb');
		$from = $this->argument('from');
		$timeout = $this->argument('tm');
		set_time_limit($timeout);

		$photos = Photo::whereNull('takestamp')->offset($from)->limit($argument)->get();
		if (count($photos) == 0) {
			$this->line('No pictures requires takedate updates.');

			return false;
		}

		$i = $from;
		foreach ($photos as $photo) {
			$url = Storage::path('big/' . $photo->url);
			if (file_exists($url)) {
				$info = $metadataExtractor->extract($url, $photo->type);
				if ($info['takestamp'] != null) {
					$photo->takestamp = $info['takestamp'];
					if ($photo->save()) {
						$this->line($i . ': Takestamp updated for ' . $photo->title);
					} else {
						$this->line($i . ': Failed to update takestamp for ' . $photo->title);
					}
				} else {
					$this->line($i . ': Could not get Takestamp data for ' . $photo->title . '.');
				}
			} else {
				$this->line($i . ': File does not exist for ' . $photo->title . '.');
			}
			$i++;
		}
	}
}
