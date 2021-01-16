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
	protected $signature = 'lychee:takedate {from=0 : from which do we start} {nb=5 : generate exif data if missing} {tm=600 : timeout time requirement}' .
		   '{--timestamp : use timestamps of media files if exif data missing}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update missing takedate entries';

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
		$timestamps = $this->option('timestamp');
		set_time_limit($timeout);

		if ($argument == 0) {
			$argument = PHP_INT_MAX;
		}
		$photos = Photo::whereNull('takestamp')->offset($from)->limit($argument)->get();
		if (count($photos) == 0) {
			$this->line('No pictures requires takedate updates.');

			return false;
		}

		$i = $from - 1;
		foreach ($photos as $photo) {
			$url = Storage::path('big/' . $photo->url);
			$i++;
			if (!file_exists($url)) {
				$this->line($i . ': File does not exist for ' . $photo->title . '.');
				continue;
			}
			$info = $metadataExtractor->extract($url, $photo->type);
			$stamp = $info['takestamp'];
			if ($stamp != null) {
				$photo->takestamp = $stamp;
				if ($photo->save()) {
					$this->line($i . ': Takestamp updated for ' . $photo->title);
				} else {
					$this->line($i . ': Failed to update takestamp for ' . $photo->title);
				}
				continue;
			}
			if (!$timestamps) {
				$this->line($i . ': Could not get Takestamp data for ' . $photo->title . '.');
				continue;
			}
			if (is_link($url)) {
				$url = readlink($url);
			}
			$photo->created_at = filemtime($url);
			if ($photo->save()) {
				$this->line($i . ': Created_at updated for ' . $photo->title);
			} else {
				$this->line($i . ': Failed to update created_at for ' . $photo->title);
			}
		}
	}
}
