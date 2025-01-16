<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Assets\Features;
use App\Console\Commands\Utilities\Colorize;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Exceptions\UnexpectedException;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\SymLink;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use function Safe\readlink;
use function Safe\scandir;
use function Safe\unlink;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class Ghostbuster extends Command
{
	/**
	 * Add color to the command line output.
	 */
	private Colorize $col;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:ghostbuster
	{--removeDeadSymLinks=0 : Removes dead symlinks and the photos pointing to them}
	{--removeZombiePhotos=0 : Removes photos pointing to non-existing files}
	{--dryrun=1 : Dry Run default is True}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Physically remove pictures which are not in the database';

	/**
	 * Create a new command instance.
	 *
	 * @param Colorize $colorize
	 *
	 * @throws SymfonyConsoleException
	 */
	public function __construct(Colorize $colorize)
	{
		parent::__construct();

		$this->col = $colorize;
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle(): int
	{
		try {
			// The asymmetry in the three lines below regarding `=== true`
			// and `!== false` is by intention for improved safety.
			// `filter_var` is tri-state and returns `null` for an
			//  unrecognized boolean value.
			// In case of errors, i.e. in the `null` case, we want the first
			// two to default to `false` and the third to default to `true`.
			$removeDeadSymLinks = filter_var($this->option('removeDeadSymLinks'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
			$removeZombiePhotos = filter_var($this->option('removeZombiePhotos'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
			$dryrun = filter_var($this->option('dryrun'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;
			$uploadDisk = Features::active('use-s3')
				? Storage::disk(StorageDiskType::S3->value)
				: Storage::disk(StorageDiskType::LOCAL->value);
			$symlinkDisk = Storage::disk(SymLink::DISK_NAME);
			$isLocalDisk = $uploadDisk->getAdapter() instanceof LocalFilesystemAdapter;

			$this->line('');
			if (!$isLocalDisk) {
				$this->line($this->col->red('Using non-local disk to store images, USE AT YOUR OWN RISKS! This code is not battle tested.'));
				$this->line('');
			}

			if ($removeDeadSymLinks && !$isLocalDisk) {
				$this->line($this->col->yellow('Removal of dead symlinks requested, but filesystem does not support symlinks.'));
				$this->line('Proceeding as if removeDeadSymlinks was not set.');
				$this->line('');
				$removeDeadSymLinks = false;
			}
			if ($removeDeadSymLinks) {
				$this->line('Also parsing database for photos with dead symbolic links.');
				$this->line($this->col->yellow('This may modify the database.'));
				$this->line('');
			}
			if (!$dryrun) {
				$this->line($this->col->red("This is not a drill! Let's delete those files!"));
				$this->line('');
			}

			/** @var string[] $filenames */
			$filenames = $uploadDisk->allFiles();

			$totalDeadSymLinks = 0;
			$totalFiles = 0;
			$totalDbEntries = 0;

			/** @var string $filename */
			foreach ($filenames as $filename) {
				if (str_contains($filename, 'index.html')) {
					continue;
				}

				$isDeadSymlink = false;
				if ($isLocalDisk) {
					$fullPath = $uploadDisk->path($filename);
					$isDeadSymlink = is_link($fullPath) && !file_exists(readlink($fullPath));
				}

				/** @var Collection<int,Photo> $photos */
				$photos = Photo::query()
					->where('live_photo_short_path', '=', $filename)
					->get();
				/** @var Collection<int,SizeVariant> $sizeVariants */
				$sizeVariants = SizeVariant::query()
					->with('photo')
					->where('short_path', '=', $filename)
					->get();

				if ($isDeadSymlink && $removeDeadSymLinks) {
					$totalDeadSymLinks++;
					if ($dryrun) {
						$this->line(str_pad($filename, 50) . $this->col->red(' is dead symlink and would be removed') . '.');
					} else {
						// Laravel apparently doesn't think dead symlinks 'exist', so use low-level commands
						unlink($uploadDisk->path($filename));
						$this->line(str_pad($filename, 50) . $this->col->red(' removed') . '.');
						$totalDbEntries += $sizeVariants->count() + $photos->count();
						/** @var SizeVariant $sizeVariant */
						foreach ($sizeVariants as $sizeVariant) {
							$sizeVariant->photo->delete();
						}
						/** @var Photo $photo */
						foreach ($photos as $photo) {
							$photo->live_photo_short_path = null;
							$photo->save();
						}
					}
				} elseif ($photos->count() + $sizeVariants->count() === 0) {
					// Remove orphaned files
					$totalFiles++;
					if ($dryrun) {
						$this->line(str_pad($filename, 50) . $this->col->red(' would be removed') . '.');
					} else {
						$uploadDisk->delete($filename);
						$this->line(str_pad($filename, 50) . $this->col->red(' removed') . '.');
					}
				}
			}
			$this->line('');

			if ($removeZombiePhotos) {
				$sizeVariants = SizeVariant::query()
					->with('photo')
					->get();
				/** @var SizeVariant $sizeVariant */
				foreach ($sizeVariants as $sizeVariant) {
					if ($sizeVariant->getFile()->exists()) {
						continue;
					}
					$totalDbEntries++;
					if ($dryrun) {
						$this->line(str_pad($sizeVariant->short_path, 50) . $this->col->red(' does not exist and photo would be removed') . '.');
					} else {
						if ($sizeVariant->type === SizeVariantType::ORIGINAL) {
							$sizeVariant->photo->delete();
						} else {
							$sizeVariant->delete();
						}
						$this->line(str_pad($sizeVariant->short_path, 50) . $this->col->red(' removed') . '.');
					}
				}
			}

			$total = $totalDeadSymLinks + $totalFiles + $totalDbEntries;
			if ($total === 0) {
				$this->line($this->col->green('No pictures found to be deleted'));
			}
			if ($total > 0 && $dryrun) {
				$this->line($totalDeadSymLinks . ' dead symbolic links would be deleted.');
				$this->line($totalFiles . ' files would be deleted.');
				$this->line($totalDbEntries . ' photos would be deleted or sanitized');
				$this->line('');
				$this->line("Rerun the command '" . $this->col->yellow('php artisan lychee:ghostbuster --removeDeadSymLinks ' . ($removeDeadSymLinks ? '1' : '0') . ' --removeZombiePhotos ' . ($removeZombiePhotos ? '1' : '0') . ' --dryrun 0') . "' to effectively remove the files.");
			}
			if ($total > 0 && !$dryrun) {
				$this->line($totalDeadSymLinks . ' dead symbolic links have been deleted.');
				$this->line($totalFiles . ' files have been deleted.');
				$this->line($totalDbEntries . ' photos have been deleted or sanitized');
			}

			// Method $symlinkDisk->allFiles() crashes, if the scanned directory
			// contains symbolic links.
			// So we must use low-level methods here.
			$symlinkDiskPath = $symlinkDisk->path('');
			$symLinks = array_slice(scandir($symlinkDiskPath), 3);
			/** @var string $symLink */
			foreach ($symLinks as $symLink) {
				$fullPath = $symlinkDiskPath . $symLink;
				$isDeadSymlink = !file_exists(readlink($fullPath));
				if ($isDeadSymlink) {
					// Laravel apparently doesn't think dead symlinks 'exist', so use low-level commands
					unlink($fullPath);
					$this->line($this->col->red('removed symbolic link: ') . $fullPath);
				}
			}

			return 0;
		} catch (SymfonyConsoleException|\LogicException $e) {
			throw new UnexpectedException($e);
		}
	}
}
