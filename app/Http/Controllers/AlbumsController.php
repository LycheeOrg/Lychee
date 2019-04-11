<?php
/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\Response;
use App\User;
use Illuminate\Support\Facades\Session;

class AlbumsController extends Controller
{

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;


	/**
	 * @param AlbumFunctions $albumFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions)
	{
		$this->albumFunctions = $albumFunctions;
	}



	/**
	 * @return array|string Returns an array of albums or false on failure.
	 */
	public function get()
	{

		// caching to avoid further request
		Configs::get();

		// Initialize return var
		$return = array(
			'smartalbums' => null,
			'albums' => null,
			'shared_albums' => null,
		);

		$shared_albums = null;

		if (Session::get('login')) {
			$id = Session::get('UserID');

			/** @noinspection PhpUndefinedMethodInspection (find) */
			$user = User::find($id);
			if ($id == 0 || $user->upload) {
				$return['smartalbums'] = $this->albumFunctions->getSmartAlbums();
			}

			if ($id == 0) {
				/** @noinspection PhpUndefinedMethodInspection (where) */
				$albums = Album::where('owner_id', '=', 0)
					->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))->get();
				$shared_albums = Album::with([
					'owner',
					'children'
				])
					->where('owner_id', '<>', 0)
					->where('parent_id', '=', null)
					->orderBy('owner_id', 'ASC')
					->get();
			}
			else {
				if ($user == null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified user ('.Session::get('UserID').')');
					return Response::error('I could not find you.');
				}
				else {
					/** @noinspection PhpUndefinedMethodInspection (where) */
					$albums = Album::where('owner_id', '=', $user->id)
						->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))
						->get();
					$shared_albums = Album::get_albums_user($user->id);
				}
			}
		}
		else {
			/** @noinspection PhpUndefinedMethodInspection (where) */
			$albums = Album::where('public', '=', '1')->where('visible_hidden', '=', '1')->where('parent_id', '=', null)
				->orderBy(Configs::get_value('sortingAlbums_col'), Configs::get_value('sortingAlbums_order'))->get();
		}


		$return['albums'] = $this->albumFunctions->prepare_albums($albums);
		$return['shared_albums'] = $this->albumFunctions->prepare_albums($shared_albums);

		return $return;

	}


}
