<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands\ImageProcessing;

use App\Assets\Features;
use App\Enum\StorageDiskType;
use App\Exceptions\UnexpectedException;
use App\Jobs\UploadTrackToS3Job;
use App\Models\Track;
use App\Repositories\ConfigManager;
use Illuminate\Console\Command;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;

/**
 * CLI-055-01: mirrors {@link MoveToS3} for {@link Track}.
 */
class TrackS3Migrate extends Command
{
	public function __construct(
		protected readonly ConfigManager $config_manager,
	) {
		parent::__construct();
	}

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:track_s3_migrate {limit=5 : number of tracks to move to s3} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Migrate existing local tracks to the configured S3 bucket';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		if (Features::inactive('use-s3')) {
			$this->error('S3 support is not activated.');

			return 0;
		}
		// @codeCoverageIgnoreStart
		try {
			$limit = (int) $this->argument('limit');
			$timeout = (int) $this->argument('tm');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$tracks = Track::query()
				->where('disk', '=', StorageDiskType::LOCAL->value)
				->limit($limit)
				->get();
			if (count($tracks) === 0) {
				$this->line('No files require migrations.');

				return 0;
			}
			$owner_id = $this->config_manager->getValueAsInt('owner_id');
			foreach ($tracks as $track) {
				$this->line('Moving ' . $track->file_name . ' to S3.');
				UploadTrackToS3Job::dispatch($track, $owner_id);
			}

			return 0;
		} catch (\Throwable $e) {
			throw new UnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}
}
