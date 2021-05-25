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
	protected $signature = 'lychee:takedate' .
			'{from=0 : index of first record}' .
			'{nb=5 : number of records to retrieve (0 to retrieve all)}' .
			'{tm=600 : maximum execution time (in seconds)}' .
			'{--timestamp : use timestamps of media files if exif data missing}' .
			'{--force : force processing of all media files}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update missing takedate entries from exif data';

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
		$force = $this->option('force');
		set_time_limit($timeout);

		if ($argument == 0) {
			$argument = PHP_INT_MAX;
		}
		if ($force) {
			$photos = Photo::offset($from)->limit($argument)->get();
		} else {
			$photos = Photo::whereNull('takestamp')->offset($from)->limit($argument)->get();
		}
		if (count($photos) == 0) {
			$this->line('No pictures require takedate updates.');

			return false;
		}

		$i = $from - 1;
		foreach ($photos as $photo) {
			$url = Storage::path('big/' . $photo->url);
			$i++;
			if (!file_exists($url)) {
				$this->line($i . ': File ' . $url . ' not found for ' . $photo->title . '.');
				continue;
			}
			$info = $metadataExtractor->extract($url, $photo->type);
			$stamp = $info['takestamp'];
			if ($stamp != null) {
				if ($stamp == $photo->takestamp) {
					$this->line($i . ': Takestamp up to date for ' . $photo->title);
					continue;
				}
				$photo->takestamp = $stamp;
				if ($photo->save()) {
					$this->line($i . ': Takestamp updated to ' . $stamp . ' for ' . $photo->title);
				} else {
					$this->line($i . ': Failed to update takestamp for ' . $photo->title);
				}
				continue;
			}
			if (!$timestamps) {
				$this->line($i . ': Failed to get Takestamp data for ' . $photo->title . '.');
				continue;
			}
			if (is_link($url)) {
				$url = readlink($url);
			}
			$created_at = filemtime($url);
			if ($created_at == $photo->created_at->timestamp) {
				$this->line($i . ': Created_at up to date for ' . $photo->title);
				continue;
			}
			$photo->created_at = $created_at;
			if ($photo->save()) {
				$this->line($i . ': Created_at updated to ' . $photo->created_at->format('d M Y \a\t H:i') . ' for ' . $photo->title);
			} else {
				$this->line($i . ': Failed to update created_at for ' . $photo->title);
			}
		}
	}
}
