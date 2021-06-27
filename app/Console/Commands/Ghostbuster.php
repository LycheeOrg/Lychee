<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\Facades\Helpers;
use App\Models\Photo;
use Illuminate\Console\Command;
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
	protected $signature = 'lychee:ghostbuster {removeDeadSymLinks=0 : Remove Photos with dead symlinks} {dryrun=1 : Dry Run default is True}';

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
		$this->line('');
		$removeDeadSymLinks = (bool) $this->argument('removeDeadSymLinks');
		$dryrun = (bool) $this->argument('dryrun');
		if ($removeDeadSymLinks) {
			$this->line('Also parsing database for pictures which point to non-existing files.');
			$this->line($this->col->yellow('This may modify the database.'));
			$this->line('');
		}
		if (!$dryrun) {
			$this->line($this->col->red("This is not a drill! Let's delete those files!"));
			$this->line('');
		}

		$path = Storage::path('big');
		$filenames = array_slice(scandir($path), 2);
		$total = 0;

		foreach ($filenames as $filename) {
			if ($filename == 'index.html') {
				continue;
			}

			$isDeadSymlink = is_link($path . '/' . $filename) && !file_exists(readlink($path . '/' . $filename));
			$photos = Photo::query()->where(function ($query) use ($filename) {
				return $query->where('filename', '=', $filename)->orWhere('live_photo_filename', '=', $filename);
			})->get();

			if (count($photos) === 0 || ($isDeadSymlink && $removeDeadSymLinks)) {
				$photoName = explode('.', $filename);

				$to_delete = [];
				$to_delete[] = 'thumb/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'thumb/' . $photoName[0] . '@2x.jpeg';

				// for videos
				$to_delete[] = 'small/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'small/' . $photoName[0] . '@2x.jpeg';
				$to_delete[] = 'medium/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'medium/' . $photoName[0] . '@2x.jpeg';

				// for normal pictures
				$to_delete[] = 'small/' . $filename;
				$to_delete[] = 'small/' . Helpers::ex2x($filename);
				$to_delete[] = 'medium/' . $filename;
				$to_delete[] = 'medium/' . Helpers::ex2x($filename);
				$to_delete[] = 'big/' . $filename;

				foreach ($to_delete as $del) {
					$delete = 0;
					if (Storage::exists($del)) {
						$delete = 1;
					} elseif (file_exists($path . '/' . $del)) {
						// symbolic link...
						$delete = 2;
					}

					if ($delete > 0) {
						$total++;
						if ($dryrun) {
							$this->line(str_pad($del, 50) . $this->col->red(' file will be removed') . '.');
						} else {
							if ($delete == 1) {
								Storage::delete($del);
							} else {
								// symbolic link
								unlink($path . '/' . $del);
							}
							$this->line($this->col->red('removed file: ') . $del);
						}
					}
				}

				if ($isDeadSymlink && $removeDeadSymLinks) {
					/** @var Photo $photo */
					foreach ($photos as $photo) {
						if ($dryrun) {
							$this->line(str_pad($photo->short_path, 50) . $this->col->red(' photo will be removed') . '.');
						} else {
							// Laravel apparently doesn't think dead symlinks 'exist', so manually remove the original here.
							unlink($path . '/' . $filename);

							$photo->predelete();
							$photo->delete();

							$this->line($this->col->red('removed photo: ') . $photo->short_path);
						}
					}
				}
			}
		}
		$this->line('');

		if ($total == 0) {
			$this->line($this->col->green('No pictures found to be deleted'));
		}
		if ($total > 0 && $dryrun) {
			$this->line($total . ' pictures will be deleted.');
			$this->line('');
			$this->line("Rerun the command '" . $this->col->yellow('php artisan lychee:ghostbuster ' . ($removeDeadSymLinks ? '1' : '0') . ' 0') . "' to effectively remove the files.");
		}
		if ($total > 0 && !$dryrun) {
			$this->line($total . ' pictures have been deleted.');
		}

		$sym_dir = Storage::drive('symbolic')->path('');
		$syms = array_slice(scandir($sym_dir), 3);

		foreach ($syms as $sym) {
			$link_path = $sym_dir . $sym;
			if (!file_exists(readlink($link_path))) {
				unlink($link_path);
				$this->line($this->col->red('removed symbolic link: ') . $link_path);
			}
		}

		return 1;
	}
}
