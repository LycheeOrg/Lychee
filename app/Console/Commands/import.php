<?php

namespace App\Console\Commands;

use App\Album;
use App\Http\Controllers\ImportController;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\PhotoFunctions;
use Exception;
use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class import extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:import {dir : directory to import} {--flatten} {--album_id= : Album ID to import to}';
//							{--owner_id=0 : User}';
// We import as admin as we are in CLI

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import a flattened directory into an album';

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
	 * @param Collection $albums
	 * @param int $parent_id
	 * @param int $indent
	 * @param int $map
	 * @param bool $is_last
	 * @return void
	 */
	public function print_album(Collection $albums, int $parent_id, int $indent = 0, int $map = 0, bool $is_last = false)
	{
		$child_albums = $albums->where('parent_id', '=', $parent_id);
		$line_indent = '';
		$i = $indent - 1;
		while ($i > 0) {
			$i--;
			if ((($map >> $i) & 1) == 1) {
				$line_indent = '| ' . $line_indent;
			}
			else {
				$line_indent = '  ' . $line_indent;
			}
		}

		$tile = $is_last ? '└ ' : '├ ';

		if ($parent_id != 0) {
			$own_album = $albums->where('id', '=', $parent_id)->first();
			$this->line(sprintf("%s%s%s (id: %d)",
				$line_indent,
				$tile,
				$own_album['title'],
				$own_album['id']
			));
		}

		$map_next = ($map << 1) | !$is_last;
		while ($child_albums->count()) {
			$child_album = $child_albums->pop();
			$this->print_album($albums, $child_album->id, $indent + 1, $map_next, $child_albums->count() == 0);
		}
		if ($is_last)
		{
			$this->line(sprintf("%s",$line_indent));
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
		$import_controller = new ImportController($this->photoFunctions, $this->albumFunctions);
		Session::put('UserID', $owner_id);;

		if ($album_id == null) {
			$album_title = null;
			$albums = Album::all([
				'id',
				'title',
				'parent_id'
			]);
			$this->print_album($albums, 0 /* parent */, 0 /* indent */);
			do {
				if ($album_title != null) {
					$this->line('No album '.$album_title.' found. Try again.');
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


		$dir_iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $file) {
			if ($file->isDir()) {
				continue;
			}

			$prefix = "Processing file ".$file;
			try {
				$ret = $import_controller->photo($file, $album_id);
			} catch (Exception $e) {
				$this->error($prefix." ERROR");
				$this->error($e);
				continue;
			}
			$this->info($prefix." OK");
		}
		$this->info("Done importing.");
	}
}
