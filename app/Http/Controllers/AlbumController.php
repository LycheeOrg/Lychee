<?php

namespace App\Http\Controllers;

use App\Album;
use App\Helpers;
use App\Logs;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;


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
        $request->validate(['title' => 'string|required|max:100', 'parent_id' => 'int|nullable']);

        $num = Album::where('id','=',$request['parent_id'])->count();
		// id cannot be 0, so by definition if $parent_id is 0 then...

        $album = new Album();
        $album->id = Helpers::generateID();
        $album->title = $request['title'];
        $album->description = '';
        $album->owner_id = Session::get('UserID');
        $album->parent_id = $num == 0 ? null : $request['parent_id'];
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

	function setLicense(Request $request){

		$request->validate([
			'albumID' => 'required|string',
			'license' => 'required|string'
		]);

		$album = Album::find($request['albumID']);

		if ($album == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
			return 'false';
		}

		$licenses = [ 'none', 'reserved', 'CC0', 'CC-BY', 'CC-BY-ND', 'CC-BY-SA', 'CC-BY-ND', 'CC-BY-NC-ND', 'CC-BY-SA'];
		$found = false;
		$i = 0;
		while(!$found && $i < count($licenses))
		{
			if ($licenses[$i] == $request['license']) $found = true;
			$i++;
		}
		if(!$found)
		{
			Logs::error(__METHOD__,__LINE__, 'wrong kind of license: '.$request['license']);
			return Response::error('wrong kind of license!');
		}

		$album->license = $request['license'];
		return $album->save() ? 'true' : 'false';
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
	        $no_error &= $album->predelete();
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
            Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');
            return 'false';
        }

        $photos = Photo::whereIn('album_id',$albumIDs)->get();
        $no_error = true;
        foreach ($photos as $photo)
        {
            $photo->album_id = $albumID;
            $no_error &= $photo->save();
        }

	    $albums = Album::whereIn('parent_id',$albumIDs)->get();
	    $no_error = true;
	    foreach ($albums as $album)
	    {
		    $album->parent_id = $albumID;
		    $no_error &= $album->save();
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

	function move(Request $request)
	{
		$request->validate(['albumIDs' => 'string|required']);

		// Convert to array
		$albumIDs = explode(',', $request['albumIDs']);
		// Get first albumID
		$albumID = array_shift($albumIDs);

		if($albumID != 0)
		{
			$album = Album::find($albumID);
			if ($album===null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');
				return 'false';
			}
		}

		$albums = Album::whereIn('id',$albumIDs)->get();
		$no_error = true;
		foreach ($albums as $album)
		{
			$album->parent_id = ($albumID == 0 ? null : $albumID) ;
			$no_error &= $album->save();
		}

		Album::reset_takestamp();
		return $no_error ? 'true' : 'false';
	}

	function getArchive(Request $request)
	{

		// Illicit chars
		$badChars =	array_merge(
			array_map('chr', range(0,31)),
			array("<", ">", ":", '"', "/", "\\", "|", "?", "*")
		);

		$request->validate([
			'albumID' => 'required|string',
		]);

		switch ($request['albumID']) {

			case 'f': $zipTitle = 'Starred';    $photos_sql = Photo::select_stars(Photo::OwnedBy(Session::get('UserID'))); break;
			case 's': $zipTitle = 'Public';     $photos_sql = Photo::select_public(Photo::OwnedBy(Session::get('UserID'))); break;
			case 'r': $zipTitle = 'Recent';     $photos_sql = Photo::select_recent(Photo::OwnedBy(Session::get('UserID'))); break;
			case '0': $zipTitle = 'Unsorted';   $photos_sql = Photo::select_unsorted(Photo::OwnedBy(Session::get('UserID'))); break;
			default:
				$album = Album::find($request['albumID']);
				if ($album===null) {
					Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
					return 'false';
				}
				$zipTitle = $album->title;

				$photos_sql  = Photo::set_order(Photo::where('album_id','=',$request['albumID']));

				// we do not provide pictures from sub albums but it would be a nice thing to do later...

//				->orWhereIn('album_id',function ($query) { function ($query) use ($id)
//				{
//					$query->select('album_id')
//						->from('user_album')
//						->where('parent_id','=',$id);
//				}}));
				break;
		}

		$zipTitle = str_replace($badChars, '', $zipTitle). '.zip';

		$response = new StreamedResponse(function() use ($zipTitle, $photos_sql, $badChars)
		{

			$opt = array(
//				'comment' => 'test zip file.',
				'largeFileSize' => 100 * 1024 * 1024,
				'enableZip64'   => true,
				'send_headers'=>true,
			);

			$zip = new ZipStream($zipTitle, $opt);


			// Check if album empty
			if ($photos_sql->count()==0) {
				Logs::error(__METHOD__, __LINE__, 'Could not create ZipStream without images');
				return false;
			}


			$photos = $photos_sql->get();
			foreach ($photos as $photo)
			{
				$title = str_replace($badChars, '', $photo->title);
				$url = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG') . $photo->url;

				if (!isset($title)||$title==='') $title = 'Untitled';
				// Check if readable
				if (!@is_readable($url)) {
					Logs::error(__METHOD__, __LINE__, 'Original photo missing: ' . $url);
					continue;
				}

				// Get extension of image
				$extension = Helpers::getExtension($url, false);
				// Set title for photo
				$zipFileName = $zipTitle . '/' . $title . $extension;
				// Check for duplicates
				if (!empty($files)) {
					$i = 1;
					while (in_array($zipFileName, $files)) {
						// Set new title for photo
						$zipFileName = $zipTitle . '/' . $title . '-' . $i . $extension;
						$i++;
					}
				}
				// Add to array
				$files[] = $zipFileName;

				# add a file named 'some_image.jpg' from a local file 'path/to/image.jpg'
				$zip->addFileFromPath($zipFileName, $url);

			}

			# finish the zip stream
			$zip->finish();

		});

		return $response;
	}

}
