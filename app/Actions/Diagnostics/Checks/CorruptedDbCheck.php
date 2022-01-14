<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Metadata\LycheeVersion;
use Illuminate\Support\Facades\DB;

class CorruptedDbCheck implements DiagnosticCheckInterface
{
	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	/**
	 * @var array
	 */
	private $versions;

	/**
	 * @param LycheeVersion $lycheeVersion
	 * @param array caching the return of lycheeVersion->get()
	 */
	public function __construct(
		LycheeVersion $lycheeVersion
	) {
		$this->lycheeVersion = $lycheeVersion;

		$this->versions = $this->lycheeVersion->get();
	}

	public function check(array &$errors): void
	{
		if ($this->versions['DB']['version'] < '4.5.0') {
			try {
				$id_albums = DB::table('user_album')
					->select('album_id')
					->whereNotIn('album_id', function ($q) {
						$q->select('id')->from('albums');
					})->get();
				$id_albums->map(fn ($e) => $e . ' is missing in the albums table, remove it from the user_albums table.')->each(function ($item, $key) use (&$errors) {
					$errors[] = $item;
				});

				$id_users = DB::table('user_album')->select('user_id')->whereNotIn('user_id', function ($q) {
					$q->select('id')->from('users');
				})->get();
				$id_users->map(fn ($e) => $e . ' is missing in the users table, remove it from the user_albums table.')->each(function ($item, $key) use (&$errors) {
					$errors[] = $item;
				});
			} catch (\Exception $e) {
				$errors[] = $e->getMessage();
			}
		}
	}
}

// $table->bigInteger('album_id')->unsigned()->nullable()->default(null)->index();
// $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
// $table->integer('user_id')->unsigned()->index();
// $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');