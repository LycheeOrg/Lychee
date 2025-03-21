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
			$remove_dead_sym_links = filter_var($this->option('removeDeadSymLinks'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
			$remove_zombie_photos = filter_var($this->option('removeZombiePhotos'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
			$dryrun = filter_var($this->option('dryrun'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;
			$upload_disk = Features::active('use-s3')
				? Storage::disk(StorageDiskType::S3->value)
				: Storage::disk(StorageDiskType::LOCAL->value);
			$symlink_disk = Storage::disk(SymLink::DISK_NAME);
			$is_local_disk = $upload_disk->getAdapter() instanceof LocalFilesystemAdapter;

			$this->line('');
			if (!$is_local_disk) {
				$this->line($this->col->red('Using non-local disk to store images, USE AT YOUR OWN RISKS! This code is not battle tested.'));
				$this->line('');
			}

			if ($remove_dead_sym_links && !$is_local_disk) {
				$this->line($this->col->yellow('Removal of dead symlinks requested, but filesystem does not support symlinks.'));
				$this->line('Proceeding as if removeDeadSymlinks was not set.');
				$this->line('');
				$remove_dead_sym_links = false;
			}
			if ($remove_dead_sym_links) {
				$this->line('Also parsing database for photos with dead symbolic links.');
				$this->line($this->col->yellow('This may modify the database.'));
				$this->line('');
			}
			if (!$dryrun) {
				$this->line($this->col->red("This is not a drill! Let's delete those files!"));
				$this->line('');
			}

			/** @var string[] $filenames */
			$filenames = $upload_disk->allFiles();

			$total_dead_sym_links = 0;
			$total_files = 0;
			$total_db_entries = 0;

			/** @var string $filename */
			foreach ($filenames as $filename) {
				if (str_contains($filename, 'index.html')) {
					continue;
				}

				$is_dead_symlink = false;
				if ($is_local_disk) {
					$full_path = $upload_disk->path($filename);
					$is_dead_symlink = is_link($full_path) && !file_exists(readlink($full_path));
				}

				/** @var Collection<int,Photo> $photos */
				$photos = Photo::query()
					->where('live_photo_short_path', '=', $filename)
					->get();
				/** @var Collection<int,SizeVariant> $sizeVariants */
				$size_variants = SizeVariant::query()
					->with('photo')
					->where('short_path', '=', $filename)
					->get();

				if ($is_dead_symlink && $remove_dead_sym_links) {
					$total_dead_sym_links++;
					if ($dryrun) {
						$this->line(str_pad($filename, 50) . $this->col->red(' is dead symlink and would be removed') . '.');
					} else {
						// Laravel apparently doesn't think dead symlinks 'exist', so use low-level commands
						unlink($upload_disk->path($filename));
						$this->line(str_pad($filename, 50) . $this->col->red(' removed') . '.');
						$total_db_entries += $size_variants->count() + $photos->count();
						/** @var SizeVariant $sizeVariant */
						foreach ($size_variants as $size_variant) {
							$size_variant->photo->delete();
						}
						/** @var Photo $photo */
						foreach ($photos as $photo) {
							$photo->live_photo_short_path = null;
							$photo->save();
						}
					}
				} elseif ($photos->count() + $size_variants->count() === 0) {
					// Remove orphaned files
					$total_files++;
					if ($dryrun) {
						$this->line(str_pad($filename, 50) . $this->col->red(' would be removed') . '.');
					} else {
						$upload_disk->delete($filename);
						$this->line(str_pad($filename, 50) . $this->col->red(' removed') . '.');
					}
				}
			}
			$this->line('');

			if ($remove_zombie_photos) {
				$size_variants = SizeVariant::query()
					->with('photo')
					->get();
				/** @var SizeVariant $sizeVariant */
				foreach ($size_variants as $size_variant) {
					if ($size_variant->getFile()->exists()) {
						continue;
					}
					$total_db_entries++;
					if ($dryrun) {
						$this->line(str_pad($size_variant->short_path, 50) . $this->col->red(' does not exist and photo would be removed') . '.');
					} else {
						if ($size_variant->type === SizeVariantType::ORIGINAL) {
							$size_variant->photo->delete();
						} else {
							$size_variant->delete();
						}
						$this->line(str_pad($size_variant->short_path, 50) . $this->col->red(' removed') . '.');
					}
				}
			}

			$total = $total_dead_sym_links + $total_files + $total_db_entries;
			if ($total === 0) {
				$this->line($this->col->green('No pictures found to be deleted'));
			}
			if ($total > 0 && $dryrun) {
				$this->line($total_dead_sym_links . ' dead symbolic links would be deleted.');
				$this->line($total_files . ' files would be deleted.');
				$this->line($total_db_entries . ' photos would be deleted or sanitized');
				$this->line('');
				$this->line("Rerun the command '" . $this->col->yellow('php artisan lychee:ghostbuster --removeDeadSymLinks ' . ($remove_dead_sym_links ? '1' : '0') . ' --removeZombiePhotos ' . ($remove_zombie_photos ? '1' : '0') . ' --dryrun 0') . "' to effectively remove the files.");
			}
			if ($total > 0 && !$dryrun) {
				$this->line($total_dead_sym_links . ' dead symbolic links have been deleted.');
				$this->line($total_files . ' files have been deleted.');
				$this->line($total_db_entries . ' photos have been deleted or sanitized');
			}

			// Method $symlinkDisk->allFiles() crashes, if the scanned directory
			// contains symbolic links.
			// So we must use low-level methods here.
			$symlink_disk_path = $symlink_disk->path('');
			$sym_links = array_slice(scandir($symlink_disk_path), 3);
			/** @var string $symLink */
			foreach ($sym_links as $sym_link) {
				$full_path = $symlink_disk_path . $sym_link;
				$is_dead_symlink = !file_exists(readlink($full_path));
				if ($is_dead_symlink) {
					// Laravel apparently doesn't think dead symlinks 'exist', so use low-level commands
					unlink($full_path);
					$this->line($this->col->red('removed symbolic link: ') . $full_path);
				}
			}

			return 0;
		} catch (SymfonyConsoleException|\LogicException $e) {
			throw new UnexpectedException($e);
		}
	}
}
