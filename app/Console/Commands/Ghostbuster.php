<?php

namespace App\Console\Commands;

use App\Console\Commands\Utilities\Colorize;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;
use Storage;

class Ghostbuster extends Command
{
	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

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
	 * @param PhotoFunctions $photoFunctions
	 *
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions, Colorize $colorize)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
		$this->col = $colorize;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->line('');
		$removeDeadSymLinks = (bool) $this->argument('removeDeadSymLinks');
		$dryrun = (bool) $this->argument('dryrun');
		if ($removeDeadSymLinks) {
			$this->line('Also parsing database for pictures where the url does not point to an existing file.');
			$this->line($this->col->yellow('This may modify the database.'));
			$this->line('');
		}
		if (!$dryrun) {
			$this->line($this->col->red("This is not a drill! Let's delete those files!"));
			$this->line('');
		}

		$path = Storage::path('big');
		$files = array_slice(scandir($path), 2);
		$total = 0;

		foreach ($files as $url) {
			if ($url == 'index.html') {
				continue;
			}

			$isDeadSymlink = is_link($path . '/' . $url) && !file_exists(readlink($path . '/' . $url));
			$photos = Photo::where(function ($query) use ($url) {
				return $query->where('url', '=', $url)->orWhere('livePhotoUrl', '=', $url);
			})->get();

			if (count($photos) === 0 || ($isDeadSymlink && $removeDeadSymLinks)) {
				$photoName = explode('.', $url);

				$to_delete = [];
				$to_delete[] = 'thumb/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'thumb/' . $photoName[0] . '@2x.jpeg';

				// for videos
				$to_delete[] = 'small/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'small/' . $photoName[0] . '@2x.jpeg';
				$to_delete[] = 'medium/' . $photoName[0] . '.jpeg';
				$to_delete[] = 'medium/' . $photoName[0] . '@2x.jpeg';

				// for normal pictures
				$to_delete[] = 'small/' . $url;
				$to_delete[] = 'small/' . $photoName[0] . '@2x.' . $photoName[1];
				$to_delete[] = 'medium/' . $url;
				$to_delete[] = 'medium/' . $photoName[0] . '@2x.' . $photoName[1];
				$to_delete[] = 'big/' . $url;

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
					foreach ($photos as $photo) {
						if ($dryrun) {
							$this->line(str_pad($photo->url, 50) . $this->col->red(' photo will be removed') . '.');
						} else {
							// Laravel apparently doesn't think dead symlinks 'exist', so manually remove the original here.
							unlink($path . '/' . $url);

							$photo->predelete();
							$photo->delete();

							$this->line($this->col->red('removed photo: ') . $photo->url);
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
