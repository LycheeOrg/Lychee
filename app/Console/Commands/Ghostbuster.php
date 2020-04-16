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
	protected $signature = 'lychee:ghostbuster {dryrun=1 : Dry Run default is True}';

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
		$dryrun = (bool) $this->argument('dryrun');

		$files = Storage::allFiles('big');
		$total = 0;

		foreach ($files as $file) {
			$url = substr($file, 4);
			if ($url == 'index.html') {
				continue;
			}
			$c = Photo::where('url', '=', $url)->count();

			if ($c == 0) {
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
					if (Storage::exists($del)) {
						$total++;
						if ($dryrun) {
							$this->line(str_pad($del, 50) . $this->col->red(' will be removed') . '.');
						} else {
							Storage::delete($del);
							$this->line($this->col->red('removed file: ') . $del);
						}
					}
				}
				$this->line('');
			}
		}

		if ($total == 0) {
			$this->line($this->col->green('No pictures found to be deleted'));
		}
		if ($total > 0 && $dryrun) {
			$this->line($total . ' pictures will be deleted.');
			$this->line('');
			$this->line("Rerun the command '" . $this->col->yellow('php artisan lychee:ghostbuster 0') . "' to effectively remove the files.");
		}
		if ($total > 0 && !$dryrun) {
			$this->line($total . ' pictures have been deleted.');
		}

		$sym_dir = Storage::drive('symbolic')->path('');
		$syms = array_slice(scandir($sym_dir), 3);

		foreach ($syms as $sym) {
			$link_path = $sym_dir . '/' . $sym;
			if (!file_exists(readlink($link_path))) {
				unlink($link_path);
				$this->line($this->col->red('removed symbolic link: ') . $link_path);
			}
		}

		return 1;
	}
}
