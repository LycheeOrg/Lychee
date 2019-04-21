<?php

namespace App\Console\Commands;

use App\Configs;
use App\Http\Controllers\ImportController;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\Helpers;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class import extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:import {dir : directory to import} {--flatten} {--album_id= : Album ID to import to}
							{--owner_id=0 : User}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import a flattened directory into a directory';

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 * @param AlbumFunctions $albumFunctions
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions, AlbumFunctions $albumFunctions)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
		$this->albumFunctions = $albumFunctions;
	}

	/**
	 * Prints the album tree in a DFS fashion
	 *
	 * @param albums $photoFunctions
	 * @return void
	 */
	public function print_album(\Illuminate\Database\Eloquent\Collection $albums, int $parent_id, int $indent=0)
	{
		$child_albums = $albums->where('parent_id', '=', $parent_id);
		$line_indent = '';
		for ($i = 0; $i < $indent; $i++) {
			$line_indent .= ' '; // TODO tree-style
		}
		if ($parent_id != 0) {
			$own_album = $albums->where('id', '=', $parent_id)->first();
			$this->line(sprintf("%s- %s (id: %d)",
				$line_indent,
				$own_album['title'],
				$own_album['id'],
			));
		}
		foreach ($child_albums as $album) {
			$this->print_album($albums, $album->id, $indent + 1);
		}
	}


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$directory = $this->argument('dir');
		$flatten = $this->option('flatten');
		$album_id = $this->option('album_id');
		$owner_id = 0; // $this->option('owner_id');
		$import_controller = new \App\Http\Controllers\ImportController($this->photoFunctions, $this->albumFunctions);
		Session::put('UserID', $owner_id);
;

		if ($album_id == null) {
			$album_title = null;
			$headers = ['Id', 'Title'];
			$albums = \App\Album::all(['id', 'title', 'parent_id']);
			$this->print_album($albums, 0 /* parent */, 0 /* indent */);
			do {
				if ($album_title != null) {
					$this->line('No album ' . $album_title . ' found. Try again.');
				}
				$album_title = $this->ask('album to insert into (title)?');
				$sel_albums = $albums->where('title', '=', $album_title);
			} while (count($sel_albums) == 0);
			assert(count($sel_albums), 1);
			$album_id = $sel_albums->first()['id'];
			$this->line(
				sprintf(
					'Selected album "%s" (id: %d)',
					$album_title,
					$album_id
				));
		}


		$dir_iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $file) {
			if ($file->isDir()) {
				continue;
			}

			$prefix = "Processing file ".$file;
			try {
				$ret = $import_controller->photo($file, $album_id);
			} catch (\Exception $e) {
				$this->error($prefix." ERROR");
				$this->error($e);
				continue;
			}
			$this->info($prefix." OK");
		}
		$this->info("Done importing.");
	}
}
