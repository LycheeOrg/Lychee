<?php

namespace App\Http\Controllers;

use App\Album;
use App\Helpers;
use App\Logs;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AlbumController extends Controller
{
    /**
     * Add a new Album
     *
     * @param Request $request
     * @return false|string
     */
    function add(Request $request)
    {
        $request->validate(['title' => 'string|required|max:100']);

        $album = new Album();
        $album->id = Helpers::generateID();
        $album->title = $request['title'];
        $album->description = '';
        $album->owner_id = Session::get('UserID');
        $album->save();

        return	Response::json($album->id, JSON_NUMERIC_CHECK);

    }

    /**
     * Provided an albumID, returns the album.
     *
     * @param Request $request
     * @return array|string
     */
    function get(Request $request)
    {
        $request->validate(['albumID' => 'string|required']);

        $return = array();
        $return['albums'] = array();
        // Get photos
        // Get album information
        switch ($request['albumID']) {

            case 'f': $return['public'] = '0'; $photos_sql = Photo::select_stars(Photo::OwnedBy(Session::get('UserID'))); break;
            case 's': $return['public'] = '0'; $photos_sql = Photo::select_public(Photo::OwnedBy(Session::get('UserID'))); break;
            case 'r': $return['public'] = '0'; $photos_sql = Photo::select_recent(Photo::OwnedBy(Session::get('UserID'))); break;
            case '0': $return['public'] = '0'; $photos_sql = Photo::select_unsorted(Photo::OwnedBy(Session::get('UserID'))); break;
            default:
                $album = Album::find($request['albumID']);
                if ($album===null) {
                    Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                    return 'false';
                }
                $return = $album->prepareData();
                $return['albums'] = $album->get_albums();
                $photos_sql = Photo::set_order(Photo::where('album_id','=',$request['albumID']));
                break;
        }

        $previousPhotoID = '';
        $return['photos'] = array();
        $photo_counter = 0;
        $photos = $photos_sql->get();
        foreach ($photos as $photo_model) {

            // Turn data from the database into a front-end friendly format
            $photo = $photo_model->prepareData();

            // Set previous and next photoID for navigation purposes
            $photo['previousPhoto'] = $previousPhotoID;
            $photo['nextPhoto']     = '';

            // Set current photoID as nextPhoto of previous photo
            if ($previousPhotoID!=='') $return['photos'][$photo_counter - 1]['nextPhoto'] = $photo['id'];
            $previousPhotoID = $photo['id'];

            // Add to return
            $return['photos'][$photo_counter] = $photo;

            $photo_counter ++;
        }

        if ($photos_sql->count() === 0) {

            // Album empty
            $return['photos'] = false;

        } else {

            // Enable next and previous for the first and last photo
            $lastElement    = end($return['photos']); $lastElementId  = $lastElement['id'];
            $firstElement   = reset($return['photos']); $firstElementId = $firstElement['id'];

            if ($lastElementId!==$firstElementId) {
                $return['photos'][$photo_counter - 1]['nextPhoto']      = $firstElementId;
                $return['photos'][0]['previousPhoto'] = $lastElementId;
            }

        }

        $return['id']  = $request['albumID'];
        $return['num'] = $photos_sql->count();

        return $return;
    }


    /**
     * Provided the albumID and passwords, return whether the album can be accessed or not.
     *
     * @param Request $request
     * @return string
     */
    function getPublic(Request $request)
    {
        $request->validate(['albumID' => 'string|required','password' => 'string|nullable']);

        switch ($request['albumID']) {

            case 'f': return 'false';
            case 's': return 'false';
            case 'r': return 'false';
            case '0': return 'false';
            default:
                $album = Album::find($request['albumID']);
                if ($album===null) {
                    Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                    return 'false';
                }
                if($album->public == 1)
                {
                    if($album->checkPassword($request['password']))
                    {
                        if(Session::has('visible_albums'))
                        {
                            $visible_albums = Session::get('visible_albums');
                        }
                        else
                        {
                            $visible_albums = '';
                        }
                        $visible_albums = explode('|',$visible_albums);
                        $visible_albums[] = $album->id;
                        $visible_albums = implode('|',$visible_albums);
                        Session::put('visible_albums',$visible_albums);
                        return 'true';
                    }
                };
                return 'false';
        }
    }

    /**
     * Provided a title and an albumID, change the title of the album
     *
     * @param Request $request
     * @return string
     */
    function setTitle(Request $request) {
        $request->validate([
            'albumIDs' => 'integer|required',
            'title' => 'string|required|max:100'
        ]);

        $albums = Album::whereIn('id',explode(',',$request['albumIDs']))->get();

        $no_error = false;
        foreach ($albums as $album)
        {
            $album->title = $request['title'];
            $no_error |= $album->save();
        }
        return $no_error ? 'true' : 'false';
    }

    /**
     * Change the sharing properties of the album
     *
     * @param Request $request
     * @return bool|string
     */
    function setPublic(Request $request) {
        $request->validate([
            'albumID' => 'integer|required',
            'password' => 'string|nullable|max:100',
            'visible' => 'integer|required',
            'downloadable' => 'integer|required'
        ]);


        $album = Album::find($request['albumID']);

        if ($album===null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
            return false;
        }

        // Convert values
        $album->public              = ($request['public'] ==='1' ? 1 : 0);
        $album->visible_hidden      = ($request['visible'] ==='1' ? 1 : 0);
        $album->downloadable        = ($request['downloadable'] ==='1' ? 1 : 0);

        // Set public
        if(!$album->save())
            return 'false';

        // Reset permissions for photos
        if ($album->public == 1) {
            if($album->photos()->count() > 0){
                if(!$album->photos()->update(array('public' => '0')))
                    return 'false';
            }
        }

        if ($request->has('password'))
        {
            if(strlen($request['password']) > 0) {
                $album->password = bcrypt($request['password']);
            }
            else
            {
                $album->password = null;
            }
            if (!$album->save())
                return 'false';
        }
        return 'true';
    }

    /**
     * Change the description of the album
     *
     * @param Request $request
     * @return bool|string
     */
    function setDescription(Request $request) {
        $request->validate([
            'albumID' => 'integer|required',
            'description' => 'string|nullable|max:1000'
        ]);

        $album = Album::find($request['albumID']);

        if ($album===null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
            return false;
        }

        $album->description = ($request['description'] == null) ? '' : $request['description'];

        return ($album->save()) ? 'true' : 'false';
    }

    /**
     * Delete the album and all pictures in the album.
     *
     * @param Request $request
     * @return string
     */
    function delete(Request $request) {
        $request->validate([
            'albumIDs' => 'string|required',
        ]);

        $no_error = true;
        if ($request['albumIDs'] == '0')
        {
            $photos = Photo::Unsorted()->get();
            foreach ($photos as $photo)
            {
                $no_error &= $photo->predelete();
                $no_error &= $photo->delete();
            }
            return $no_error ? 'true' : 'false';
        }
        $albums = Album::whereIn('id',explode(',',$request['albumIDs']))->get();

        foreach ($albums as $album)
        {
            $photos = $album->photos();
            foreach ($photos as $photo)
            {
                $no_error &= $photo->predelete();
                $no_error &= $photos->delete();
            }
            $no_error &= $album->delete();
        }

        return $no_error ? 'true' : 'false';

    }

    /**
     * Merge albums. The first of the list is the destination of the merge
     *
     * @param Request $request
     * @return string
     */
    function merge(Request $request) {
        $request->validate([
            'albumIDs' => 'string|required',
        ]);

        // Convert to array
        $albumIDs = explode(',', $request['albumIDs']);
        // Get first albumID
        $albumID = array_shift($albumIDs);

        $album = Album::find($albumID);

        if ($album===null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
            return 'false';
        }

        $photos = Photo::whereIn('album_id',$albumIDs)->get();
        $no_error = true;
        foreach ($photos as $photo)
        {
            $photo->album_id = $albumID;
            $no_error &= $photo->save();
        }

        $albums = Album::whereIn('id',$albumIDs)->get();
        foreach ($albums as $album_t)
        {
            $album->min_takestamp = min($album->min_takestamp, $album_t->min_takestamp);
            $album->max_takestamp = max($album->max_takestamp, $album_t->max_takestamp);
            $no_error &= $album_t->delete();
        }
        $no_error &= $album->save();

        return $no_error ? 'true' : 'false';

    }

}
