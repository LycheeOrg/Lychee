<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Logs;
use App\Photo;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class AlbumsController extends Controller
{

    /**
     * @return array|string Returns an array of albums or false on failure.
     */
    public function get(Request $request) {

    	// caching to avoid further request
	    Configs::get();

        // Initialize return var
        $return = array(
            'smartalbums'   => null,
            'albums'        => null,
            'shared_albums' => null,
        );

        $shared_albums = null;

        if (Session::get('login'))
        {
            $id = Session::get('UserID');

            $user = User::find($id);
            if($id == 0 || $user->upload) $return['smartalbums'] = self::getSmartAlbums();

            if($id == 0)
            {
                $albums = Album::where('owner_id','=', 0)
                    ->orderBy(Configs::get_value('sortingAlbums_col'),Configs::get_value('sortingAlbums_order'))->get();
                $shared_albums = Album::with(['owner','children'])
	                ->where('owner_id','<>',0)
	                ->where('parent_id','=',null)
	                ->orderBy('owner_id','ASC')
	                ->get();
            }
            else if($user == null)
            {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified user ('.Session::get('UserID').')');
                return Response::error('I could not find you.');
            }
            else
            {
                $albums = Album::where('owner_id','=', $user->id)
                    ->orderBy(Configs::get_value('sortingAlbums_col'),Configs::get_value('sortingAlbums_order'))
	                ->get();
                $shared_albums = Album::get_albums_user($user->id);
            }
        }
        else
        {
            $albums = Album::where('public','=','1')->where('visible_hidden','=','1')->where('parent_id','=',null)
                ->orderBy(Configs::get_value('sortingAlbums_col'),Configs::get_value('sortingAlbums_order'))->get();
        }


        $return['albums'] = AlbumsController::prepare_albums($albums);
        $return['shared_albums'] = AlbumsController::prepare_albums($shared_albums);

        return $return;

    }


    static private function prepare_albums($albums) {

        $return = array();

        if($albums != null)
        {
            // For each album
            foreach ($albums as $album_model) {

                // Turn data from the database into a front-end friendly format
                $album = $album_model->prepareData();
	            $album['albums'] = $album_model->get_albums();

                // Thumbs
                if ((!Session::get('login') && $album_model->password === null)||
                    (Session::get('login'))) {

                    $album['sysstamp'] = $album_model['created_at'];
                    $album = $album_model->gen_thumbs($album);
                }

                // Add to return
                $return[] = $album;
            }
        }

        return $return;
    }


    static private function gen_return($return, $photos_sql, $kind)
    {
        $photos = $photos_sql->get();
        $i        = 0;

        $return[$kind] = array(
            'thumbs' => array(),
            'types'  => array(),
            'num'    => $photos_sql->count()
        );

        foreach ($photos as $photo) {
            if ($i<3) {
                $return[$kind]['thumbs'][$i] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB') . $photo->thumbUrl;
                $return[$kind]['types'][$i] = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB') . $photo->type;
                $i++;
            } else break;
        }

        return $return;
    }

    /**
     * @return array|false Returns an array of smart albums or false on failure.
     */
    private function getSmartAlbums() {

        // Initialize return var
        $return = array(
            'unsorted' => null,
            'public'   => null,
            'starred'  => null,
            'recent'   => null
        );

        /**
         * Unsorted
         */
        $photos_sql = Photo::select_unsorted(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'type'))->limit(3);
        $return = self::gen_return($return, $photos_sql, 'unsorted');

        /**
         * Starred
         */
        $photos_sql = Photo::select_stars(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'type'))->limit(3);
        $return = self::gen_return($return, $photos_sql, 'starred');

        /**
         * Public
         */
        $photos_sql = Photo::select_public(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl', 'type'))->limit(3);
        $return = self::gen_return($return, $photos_sql, 'public');

        /**
         * Recent
         */
        $photos_sql = Photo::select_recent(Photo::OwnedBy(Session::get('UserID'))->select('thumbUrl'))->limit(3);
        $return = self::gen_return($return, $photos_sql, 'recent');

        // Return SmartAlbums
        return $return;

    }

}
