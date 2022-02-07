<?php

namespace App\Console\Commands;

use App\Metadata\Extractor;
use App\Models\Photo;
use App\Models\SizeVariant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class Takedate extends Command
{
	private ConsoleSectionOutput $msgSection;
	private ProgressBar $progressBar;

	private const DATETIME_FORMAT = 'Y-m-d \a\t H:i:s (e)';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:takedate' .
	'{offset=0 : offset of the first photo to process}' .
	'{limit=50 : number of photos to process (0 means process all)}' .
	'{time=600 : maximum execution time in seconds (0 means unlimited)}' .
	'{--c|set-upload-time : additionally sets the upload time based on the creation time of the media file; ATTENTION: this option is rarely needed and potentially harmful}' .
	'{--f|force : force processing of all media files}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update missing takedate entries from exif data';

	public function __construct()
	{
		parent::__construct();
		$output = new ConsoleOutput();
		// Create an independent section for message _above_ the section
		// which holds the progress bar.
		// This way the progress bar remains on the bottom in case too
		// many warning/errors are spit out.
		$this->msgSection = $output->section();
		$this->progressBar = new ProgressBar($output->section());
		$this->progressBar->setFormat('Photo %current%/%max% [%bar%] %percent:3s%%');
	}

	/**
	 * Outputs an error message.
	 *
	 * @param string $msg the message
	 *
	 * @return void
	 */
	private function printError(string $msg): void
	{
		$this->msgSection->writeln('<error>Error:</error> ' . $msg);
	}

	/**
	 * Outputs an warning.
	 *
	 * @param string $msg the message
	 *
	 * @return void
	 */
	private function printWarning(string $msg): void
	{
		$this->msgSection->writeln('<comment>Warning:</comment> ' . $msg);
	}

	/**
	 * Outputs an informational message.
	 *
	 * @param string $msg the message
	 *
	 * @return void
	 */
	private function printInfo(string $msg): void
	{
		$this->msgSection->writeln('<info>Info:</info>  ' . $msg);
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(Extractor $metadataExtractor): int
	{
		$limit = intval($this->argument('limit'));
		$offset = intval($this->argument('offset'));
		$timeout = intval($this->argument('time'));
		$setCreationTime = boolval($this->option('set-upload-time'));
		$force = boolval($this->option('force'));
		set_time_limit($timeout);

		// For faster iteration we eagerly load the original size variant,
		// but only the original size variant
		$photoQuery = Photo::with(['size_variants' => function (HasMany $r) {
			$r->where('type', '=', SizeVariant::ORIGINAL);
		}]);

		if (!$force) {
			$photoQuery->whereNull('taken_at');
		}

		// ATTENTION: We must call `count` first, otherwise `offset` and
		// `limit` won't have an effect.
		$count = $photoQuery->count();
		if ($count === 0) {
			$this->printInfo('No pictures require takedate updates.');

			return -1;
		}

		// We must stipulate a particular order, otherwise `offset` and `limit` have random effects
		$photoQuery->orderBy('id');

		if ($offset !== 0) {
			$photoQuery->offset($offset);
		}

		if ($limit !== 0) {
			$photoQuery->limit($limit);
		}

		$this->progressBar->setMaxSteps($limit === 0 ? $count : min($count, $limit));

		// Unfortunately, `->getLazy` ignores `offset` and `limit`, so we must
		// use a regular collection which might run out of memory for large
		// values of `limit`.
		$photos = $photoQuery->get();
		/* @var Photo $photo */
		foreach ($photos as $photo) {
			$this->progressBar->advance();
			// TODO: As soon as we support AWS S3 storage, we must stop using absolute paths. However, first the EXIF extractor must be rewritten to use file streams.
			$fullPath = $photo->size_variants->getOriginal()->getFile()->getAbsolutePath();

			if (!file_exists($fullPath)) {
				$this->printError('File ' . $fullPath . ' not found for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
				continue;
			}

			$kind = $photo->isRaw() ? 'raw' : ($photo->isVideo() ? 'video' : 'photo');
			$info = $metadataExtractor->extract($fullPath, $kind);
			/* @var Carbon $stamp */
			$stamp = $info['taken_at'];
			if ($stamp !== null) {
				// Note: `equalTo` only checks if two times indicate the same
				// instant of time on the universe's timeline, i.e. equality
				// comparison is always done in UTC.
				// For example "2022-01-31 20:50 CET" is deemed equal to
				// "2022-01-31 19:50 GMT".
				// So, we must check for equality of timezones separately.
				if ($photo->taken_at->equalTo($stamp) && $photo->taken_at->timezoneName === $stamp->timezoneName) {
					$this->printInfo('Takestamp ' . $stamp->format(self::DATETIME_FORMAT) . ' up to date for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
				} else {
					$photo->taken_at = $stamp;
					$this->printInfo('Takestamp updated to ' . $photo->taken_at->format(self::DATETIME_FORMAT) . ' for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
				}
			} else {
				$this->printWarning('Failed to extract takestamp data from media file for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
			}

			if ($setCreationTime) {
				if (is_link($fullPath)) {
					$fullPath = readlink($fullPath);
				}
				$created_at = filemtime($fullPath);
				if ($created_at == $photo->created_at->timestamp) {
					$this->printInfo('Upload time up to date for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
				} else {
					$photo->created_at = Carbon::createFromTimestamp($created_at);
					$this->printInfo('Upload time updated to ' . $photo->created_at->format(self::DATETIME_FORMAT) . ' for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
				}
			}

			if (!$photo->save()) {
				$this->printError('Failed to save changes for photo "' . $photo->title . '" (ID=' . $photo->id . ').');
			}
		}

		return 0;
	}
}
