<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Contracts\Exceptions\ExternalLycheeException;
use App\Contracts\Exceptions\InternalLycheeException;
use App\Enum\SizeVariantType;
use App\Exceptions\UnexpectedException;
use App\Metadata\Extractor;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Safe\Exceptions\InfoException;
use function Safe\filemtime;
use function Safe\set_time_limit;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\String\Exception\ExceptionInterface as SymfonyStringException;

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
	protected $signature = 'lychee:takedate ' .
		'{offset=0 : offset of the first photo to process} ' .
		'{limit=50 : number of photos to process (0 means process all)} ' .
		'{time=600 : maximum execution time in seconds (0 means unlimited)} ' .
		'{--c|set-upload-time : additionally sets the upload time based on the creation time of the media file; ATTENTION: this option is rarely needed and potentially harmful} ' .
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
	 * Outputs an warning.
	 *
	 * @param string $msg the message
	 *
	 * @return void
	 */
	private function printWarning(Photo $photo, string $msg): void
	{
		$this->msgSection->writeln('<comment>Warning:</comment> Photo "' . $photo->title . '" (ID=' . $photo->id . '): ' . $msg);
	}

	/**
	 * Outputs an informational message.
	 *
	 * @param string $msg the message
	 *
	 * @return void
	 */
	private function printInfo(Photo $photo, string $msg): void
	{
		$this->msgSection->writeln('<info>Info:</info>    Photo "' . $photo->title . '" (ID=' . $photo->id . '): ' . $msg);
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			$limit = intval($this->argument('limit'));
			$offset = intval($this->argument('offset'));
			$timeout = intval($this->argument('time'));
			$setCreationTime = $this->option('set-upload-time') === true;
			$force = $this->option('force') === true;
			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			// For faster iteration we eagerly load the original size variant,
			// but only the original size variant
			$photoQuery = Photo::query()->with(['size_variants' => function ($r) {
				$r->where('type', '=', SizeVariantType::ORIGINAL);
			}]);

			if (!$force) {
				$photoQuery->whereNull('taken_at');
			}

			// ATTENTION: We must call `count` first, otherwise `offset` and
			// `limit` won't have an effect.
			$count = $photoQuery->count();
			if ($count === 0) {
				$this->line('No pictures require takedate updates.');

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
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$this->progressBar->advance();
				$localFile = $photo->size_variants->getOriginal()->getFile()->toLocalFile();

				$info = Extractor::createFromFile($localFile, filemtime($localFile->getRealPath()));
				if ($info->taken_at !== null) {
					// Note: `equalTo` only checks if two times indicate the same
					// instant of time on the universe's timeline, i.e. equality
					// comparison is always done in UTC.
					// For example "2022-01-31 20:50 CET" is deemed equal to
					// "2022-01-31 19:50 GMT".
					// So, we must check for equality of timezones separately.
					if ($photo->taken_at->equalTo($info->taken_at) && $photo->taken_at->timezoneName === $info->taken_at->timezoneName) {
						$this->printInfo($photo, 'Takestamp up-to-date.');
					} else {
						$photo->taken_at = $info->taken_at;
						$this->printInfo($photo, 'Takestamp set to ' . $photo->taken_at->format(self::DATETIME_FORMAT) . '.');
					}
				} else {
					$this->printWarning($photo, 'Failed to extract takestamp data from media file.');
				}

				if ($setCreationTime) {
					$created_at = $localFile->lastModified();
					if ($created_at === $photo->created_at->timestamp) {
						$this->printInfo($photo, 'Upload time up-to-date.');
					} else {
						$photo->created_at = Carbon::createFromTimestamp($created_at);
						$this->printInfo($photo, 'Upload time set to ' . $photo->created_at->format(self::DATETIME_FORMAT) . '.');
					}
				}

				$photo->save();
			}

			return 0;
		} catch (SymfonyConsoleException|InternalLycheeException|SymfonyStringException $e) {
			throw new UnexpectedException($e);
		}
	}
}
