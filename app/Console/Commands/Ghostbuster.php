<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\SymLink;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class Ghostbuster extends Command
{
	/**
	 * Add color to the command line output.
	 *
	 * @var Colorize
	 */
	private $col;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:ghostbuster {removeDeadSymLinks=0 : Removes dead symlinks and the photos pointing to them} {removeZombiePhotos=0: Removes photos pointing to non-existing files} {dryrun=1 : Dry Run default is True}';

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
	 * @return void
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
		$removeDeadSymLinks = (bool) $this->argument('removeDeadSymLinks');
		$removeZombiePhotos = (bool) $this->argument('removeZombiePhotos');
		$dryrun = (bool) $this->argument('dryrun');
		$uploadDisk = Storage::disk();
		$symlinkDisk = Storage::disk(SymLink::DISK_NAME);
		$isLocalDisk = ($uploadDisk->getDriver()->getAdapter() instanceof \League\Flysystem\Adapter\Local);

		$this->line('');

		if ($removeDeadSymLinks && !$isLocalDisk) {
			$this->line($this->col->yellow('Removal of dead symlinks requested, but filesystem does not support symlinks.'));
			$this->line('Proceeding as if removeDeadSymlinks was not set.');
			$this->line('');
			$removeDeadSymLinks = false;
		}
		if ($removeDeadSymLinks) {
			$this->line('Also parsing database for pictures which point to non-existing files.');
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

			/** @var Collection $sizeVariants */
			$photos = Photo::query()
				->where('live_photo_filename', '=', $filename)
				->get();
			/** @var Collection $sizeVariants */
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
				if ($uploadDisk->exists($sizeVariant->short_path)) {
					continue;
				}
				$totalDbEntries++;
				if ($dryrun) {
					$this->line(str_pad($filename, 50) . $this->col->red(' does not exist and photo would be removed') . '.');
				} else {
					if ($sizeVariant->size_variant == SizeVariant::ORIGINAL) {
						$sizeVariant->photo->delete();
					} else {
						$sizeVariant->delete();
					}
					$this->line(str_pad($filename, 50) . $this->col->red(' removed') . '.');
				}
			}
		}

		$total = $totalDeadSymLinks + $totalFiles + $totalDbEntries;
		if ($total == 0) {
			$this->line($this->col->green('No pictures found to be deleted'));
		}
		if ($total > 0 && $dryrun) {
			$this->line($totalDeadSymLinks . ' dead symbolic links would be deleted.');
			$this->line($totalFiles . ' files would be deleted.');
			$this->line($totalDbEntries . ' photos would be deleted or sanitized');
			$this->line('');
			$this->line("Rerun the command '" . $this->col->yellow('php artisan lychee:ghostbuster ' . ($removeDeadSymLinks ? '1' : '0') . ' 0') . "' to effectively remove the files.");
		}
		if ($total > 0 && !$dryrun) {
			$this->line($totalDeadSymLinks . ' dead symbolic links have been deleted.');
			$this->line($totalFiles . ' files have been deleted.');
			$this->line($totalDbEntries . ' photos have been deleted or sanitized');
		}

		/** @var string[] $filenames */
		$symLinks = $symlinkDisk->allFiles();
		foreach ($symLinks as $symLink) {
			$fullPath = $symlinkDisk->path($filename);
			$isDeadSymlink = !file_exists(readlink($fullPath));
			if ($isDeadSymlink) {
				// Laravel apparently doesn't think dead symlinks 'exist', so use low-level commands
				unlink($fullPath);
				$this->line($this->col->red('removed symbolic link: ') . $filename);
			}
		}

		return 1;
	}
}
