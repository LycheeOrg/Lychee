<?php

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Helpers;
use App\Logs;
use App\Photo;
use App\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class PhotoController extends Controller
{
    public static $validTypes = array(
        IMAGETYPE_JPEG,
        IMAGETYPE_GIF,
        IMAGETYPE_PNG
    );

    public static $validVideoTypes = array(
        "video/mp4",
        "video/ogg",
        "video/webm",
        "video/quicktime"
    );
    public static $validExtensions = array(
        '.jpg',
        '.jpeg',
        '.png',
        '.gif',
        '.ogv',
        '.mp4',
        '.webm',
        '.mov'
    );


    function get(Request $request){
        $request->validate([
            'albumID' => 'string|required', // we actually don't care about that one...
            'photoID' => 'string|required'
        ]);

        $photo = Photo::find($request['photoID']);

        // Photo not found?
        if ($photo == null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
            return 'false';
        }

        $return = $photo->prepareData();
        if ((!Session::get('login') && $return['public'] == '1')||
            (Session::get('login')) ||
            SessionController::checkAccess($request, $photo->album_id) === 1) {
            $return['original_album'] = $return['album'];
            $return['album']          = $request['albumID'];
            return $return;
        }

        Logs::error(__METHOD__, __LINE__, 'Accessing non public photo: '.$photo->id);
        return 'false';
    }



    function add(Request $request)
    {


        $request->validate([
            'albumID' => 'string|required',
            '0' => 'required',
//            '0.*' => 'image|mimes:jpeg,png,jpg,gif',
            '0.*' => 'image|mimes:jpeg,png,jpg,gif,mov,webm,mp4,ogv',
//                |max:2048'
        ]);

        $id = Session::get('UserID');

        if (!$request->hasfile('0'))
            return Response::error('missing files');

        // Check permissions
        if (Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS')) === false ||
            Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_BIG')) === false ||
            Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_MEDIUM')) === false ||
            Helpers::hasPermissions(Config::get('defines.dirs.LYCHEE_UPLOADS_THUMB')) === false) {
            Logs::error(__METHOD__, __LINE__, 'An upload-folder is missing or not readable and writable');
            return Response::error('An upload-folder is missing or not readable and writable!');
        }

        switch ($request['albumID']) {
            // s for public (share)
            case 's':
                $public = 1;
                $star = 0;
                $albumID = null;
                break;

            // f for starred (fav)
            case 'f':
                $star = 1;
                $public = 0;
                $albumID = null;
                break;

            // r for recent
            case 'r':
                $public = 0;
                $star = 0;
                $albumID = null;
                break;

            // r for recent
            case '0':
                $public = 0;
                $star = 0;
                $albumID = null;
                break;

            default:
                $star = 0;
                $public = 0;
                $albumID = $request['albumID'];
                break;
        }

        // Only process the first photo in the array
        $file = $request->file('0');

        // Verify extension
        $extension = Helpers::getExtension($file->getClientOriginalName(), false);
        if (!in_array(strtolower($extension), self::$validExtensions, true)) {
            Logs::error(__METHOD__, __LINE__, 'Photo format not supported');
            return Response::error('Photo format not supported!');
        }

        // should not be needed
        // Verify video
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::$validVideoTypes, true)) {

	        if (!function_exists("exif_imagetype")) {
		        Logs::error(__METHOD__, __LINE__, 'EXIF library not loaded. Make sure exif is enabled in php.ini');
		        return Response::error('EXIF library not loaded on the server!');
	        }

            // Verify image
            $type = @exif_imagetype($file->getPathName());
            if (!in_array($type, self::$validTypes, true)) {
                Logs::error(__METHOD__, __LINE__, 'Photo type not supported');
                return Response::error('Photo type not supported!');
            }
        }

        // Generate id
        $photo = new Photo();
        $photo->id = Helpers::generateID();

        // Set paths
        $tmp_name   = $file->getPathName();
        $photo_name = md5(microtime()) . $extension;
        $path       = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') . $photo_name;

        // Calculate checksum
        $checksum = sha1_file($tmp_name);
        if ($checksum===false) {
            Logs::error(__METHOD__, __LINE__, 'Could not calculate checksum for photo');
            return Response::error('Could not calculate checksum for photo!');
        }


        $exists = Photo::exists($checksum);

        // double check that
        if ($exists!==false) {
            $photo_name = $exists->url;
            $path       = Config::get('defines.dirs.LYCHEE_UPLOADS_BIG') . $exists->url;
            $path_thumb = $exists->thumbUrl;
            $medium     = $exists->medium;
	        $small      = $exists->small;
            $exists     = true;
        }


        if ($exists===false) {

            // Import if not uploaded via web
            if (!is_uploaded_file($tmp_name)) {
                if (!@copy($tmp_name, $path)) {
                    Logs::error(__METHOD__, __LINE__, 'Could not copy photo to uploads');
                    return Response::error('Could not copy photo to uploads!');
                } else @unlink($tmp_name);
            } else {
                if (!@move_uploaded_file($tmp_name, $path)) {
                    Logs::error(__METHOD__, __LINE__, 'Could not move photo to uploads');
                    return Response::error('Could not move photo to uploads!');
                }
            }

        } else {

            // Photo already exists
            // Check if the user wants to skip duplicates
            if (Configs::get()['skipDuplicates']==='1') {
                Logs::notice(__METHOD__, __LINE__, 'Skipped upload of existing photo because skipDuplicates is activated');
                return Response::warning('This photo has been skipped because it\'s already in your library.');
            }

        }

        // Read infos
        $info = Photo::getInformations($path);
        // Use title of file if IPTC title missing
        if ($info['title']==='') $info['title'] = substr(basename($file->getClientOriginalName(), $extension), 0, 30);

        $photo->title = $info['title'];
        $photo->url = $photo_name;
        $photo->description = $info['description'];
        $photo->tags = $info['tags'];
        $photo->width = $info['width'] ? $info['width'] : 0;
        $photo->height = $info['height'] ? $info['height'] : 0;
        $photo->type = ($info['type'] ? $info['type'] : $mimeType );
        $photo->size = $info['size'];
        $photo->iso = $info['iso'];
        $photo->aperture = $info['aperture'];
        $photo->make = $info['make'];
        $photo->model = $info['model'];
        $photo->lens = $info['lens'];
        $photo->shutter = $info['shutter'];
        $photo->focal = $info['focal'];
        $photo->takestamp = $info['takestamp'];
        $photo->latitude = $info['latitude'];
        $photo->longitude = $info['longitude'];
        $photo->altitude = $info['altitude'];
        $photo->public = $public;
        $photo->owner_id = Session::get('UserID');
        $photo->star = $star;
        $photo->checksum = $checksum;
        $photo->album_id = $albumID;
        $photo->medium = 0;
	    $photo->small = 0;

        if ($exists===false) {

            // Set orientation based on EXIF data
            if ($photo->type === 'image/jpeg' && isset($info['orientation'])&&$info['orientation']!=='') {
                $adjustFile = Photo::adjustFile($path, $info);
                if ($adjustFile!==false) $info = $adjustFile;
                else Logs::notice(__METHOD__, __LINE__, 'Skipped adjustment of photo (' . $info['title'] . ')');
            }

            $photo->width = $info['width'];
            $photo->height = $info['height'];

            // Set original date
            if ($info['takestamp']!==''&& $info['takestamp']!==0) @touch($path, $info['takestamp']);

            // Create Thumb
            if(!in_array($photo->type , self::$validVideoTypes, true)) {

                if (!$photo->createThumb()) {
                    Logs::error(__METHOD__, __LINE__, 'Could not create thumbnail for photo');
                    return Response::error('Could not create thumbnail for photo!');
                }

                $path_thumb = basename($photo_name, $extension) . ".jpeg";
            }
			elseif(!defined('VIDEO_THUMB'))
            {
                Logs::notice(__METHOD__, __LINE__, 'Could not create thumbnail for video because FFMPEG is not available.');
                // Set thumb url
                $path_thumb = '';
            }
            else
            {
                if (!$photo->createVideoThumb($path, $id)){
                    Logs::error( __METHOD__, __LINE__, 'Could not create thumbnail for video');
                    $path_thumb = '';
                }
                else
                {
                    // Set thumb url
                    $path_thumb = md5($id) . '.jpeg';
                }
            }

            // Create Medium
            if ($photo->createMedium(intval(Configs::get_value('medium_max_width')), intval(Configs::get_value('medium_max_height')))) $medium = 1;
            else $medium = 0;

	        // Create Small
	        if ($photo->createMedium(intval(Configs::get_value('small_max_width')),intval(Configs::get_value('small_max_height')),'SMALL')) $small = 1;
	        else $small = 0;
        }

        $photo->thumbUrl = $path_thumb;
        $photo->medium = $medium;
	    $photo->small = $small;
        if (!$photo->save()) {
            return Response::error('Could not save photo in database!');
        }

        if($albumID != null)
        {
            $album = Album::find($albumID);
            if ($album===null) {
                Logs::error(__METHOD__, __LINE__, 'Could not find specified album');
                return 'false';
            }
            $album->update_min_max_takestamp();
            if (!$album->save()) {
                return Response::error('Could not update album takestamp in database!');
            }
        }

        return $photo->id;

    }

    function setTitle(Request $request){

        $request->validate([
            'photoIDs' => 'required|string',
            'title' => 'required|string|max:100'
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $no_error = true;
        foreach($photos as $photo)
        {
            $photo->title = $request['title'];
            $no_error |= $photo->save();
        }
        return $no_error ? 'true' : 'false';
    }

    function setStar(Request $request){

        $request->validate([
            'photoIDs' => 'required|string',
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $no_error = true;
        foreach($photos as $photo)
        {
            $photo->star = ($photo->star != 1) ? 1 : 0;
            $no_error |= $photo->save();
        }
        return $no_error ? 'true' : 'false';
    }

    function setDescription(Request $request){

        $request->validate([
            'photoID' => 'required|string',
            'description' => 'string|nullable'
        ]);

        $photo = Photo::find($request['photoID']);

        // Photo not found?
        if ($photo == null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
            return 'false';
        }

        $photo->description = $request['description'];
        return $photo->save() ? 'true' : 'false';
    }

    function setPublic(Request $request){

        $request->validate([
            'photoID' => 'required|string'
        ]);

        $photo = Photo::find($request['photoID']);

        // Photo not found?
        if ($photo == null) {
            Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
            return 'false';
        }

        $photo->public = $photo->public != 1 ? 1 : 0;
        return $photo->save() ? 'true' : 'false';
    }

	/**
	 * @param Request $request
	 * @return string
	 */
	function setTags(Request $request){

        $request->validate([
            'photoIDs' => 'required|string',
            'tags' => 'string|nullable'
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $no_error = true;
        foreach($photos as $photo)
        {
            $photo->tags = $request['tags'];
            $no_error &= $photo->save();
        }
        return $no_error ? 'true' : 'false';
    }

    function setAlbum(Request $request){

        $request->validate([
            'photoIDs' => 'required|string',
            'albumID' => 'required|string'
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $albumID = $request['albumID'];

        $no_error = true;
        foreach($photos as $photo)
        {
            $photo->album_id = ($albumID == '0') ? null : $albumID;
            $no_error &= $photo->save();
        }
        Album::reset_takestamp();
        return $no_error ? 'true' : 'false';
    }

	function setLicense(Request $request){

		$request->validate([
			'photoID' => 'required|string',
			'license' => 'required|string'
		]);

		$photo = Photo::find($request['photoID']);

		// Photo not found?
		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');
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

		$photo->license = $request['license'];
		return $photo->save() ? 'true' : 'false';
	}

    function delete(Request $request){

        $request->validate([
            'photoIDs' => 'required|string',
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $no_error = true;
        foreach($photos as $photo) {
            $no_error &= $photo->predelete();
            $no_error &= $photo->delete();
        }
        Album::reset_takestamp();
        return $no_error ? 'true' : 'false';
    }

    function duplicate(Request $request){
        $request->validate([
            'photoIDs' => 'required|string',
        ]);

        $photos = Photo::whereIn('id',explode(',',$request['photoIDs']))->get();

        $no_error = true;
        foreach($photos as $photo) {
            $duplicate = new Photo();
            $duplicate->title        = $photo->title;
            $duplicate->description  = $photo->description;
            $duplicate->url          = $photo->url;
            $duplicate->tags         = $photo->tags;
            $duplicate->public       = $photo->public;
            $duplicate->type         = $photo->type;
            $duplicate->width        = $photo->width;
            $duplicate->height       = $photo->height;
            $duplicate->size         = $photo->size;
            $duplicate->iso          = $photo->iso;
            $duplicate->aperture     = $photo->aperture;
            $duplicate->make         = $photo->make;
            $duplicate->model        = $photo->model;
	        $duplicate->lens         = $photo->lens;
            $duplicate->shutter      = $photo->shutter;
            $duplicate->focal        = $photo->focal;
            $duplicate->latitude     = $photo->latitude;
            $duplicate->longitude    = $photo->longitude;
            $duplicate->altitude     = $photo->altitude;
            $duplicate->takestamp    = $photo->takestamp;
            $duplicate->star         = $photo->star;
            $duplicate->thumbUrl     = $photo->thumbUrl;
            $duplicate->album_id     = $photo->album_id;
            $duplicate->checksum     = $photo->checksum;
            $duplicate->medium       = $photo->medium;
	        $duplicate->small        = $photo->small;
            $duplicate->owner_id     = $photo->owner_id;
            $no_error &= $duplicate->save();
        }
        return $no_error ? 'true' : 'false';

    }
}
